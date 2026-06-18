<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Controllers\PosController;
use Arancamon\ApiPhp\Models\GetModel;
use Arancamon\ApiPhp\Models\PosModel;
use Arancamon\ApiPhp\Models\PutRepository;

beforeEach(function () {
    $_ENV['API_KEY'] = 'test-key';
});

test('response returns 404 JSON when response is empty and no error', function () {
    $controller = new PosController;
    $method = new ReflectionMethod(PosController::class, 'response');

    ob_start();
    $method->invoke($controller, [], null, null);

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 404,
        'results' => 'Not Found',
        'method' => 'post',
    ]);
});

test('response returns error JSON when response is empty with error message', function () {
    $controller = new PosController;
    $method = new ReflectionMethod(PosController::class, 'response');

    ob_start();
    $method->invoke($controller, [], 'Custom error', null);

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 400,
        'results' => 'Custom error',
    ]);
});

test('response strips password field when suffix is provided', function () {
    $controller = new PosController;
    $method = new ReflectionMethod(PosController::class, 'response');

    $user = (object) ['id_user' => 1, 'email_user' => 'a@b.com', 'password_user' => 'secret'];
    $data = [$user];

    ob_start();
    $method->invoke($controller, $data, null, 'user');

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json['status'])->toBe(200);
    expect($json['results'][0])->toHaveKeys(['id_user', 'email_user']);
    expect($json['results'][0])->not->toHaveKey('password_user');
});

test('response keeps password when suffix is null', function () {
    $controller = new PosController;
    $method = new ReflectionMethod(PosController::class, 'response');

    $user = (object) ['id_user' => 1, 'email_user' => 'a@b.com', 'password_user' => 'secret'];
    $data = [$user];

    ob_start();
    $method->invoke($controller, $data, null, null);

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json['status'])->toBe(200);
    expect($json['results'][0])->toHaveKey('password_user');
});

test('postData returns insert result', function () {
    $posModel = new class extends PosModel {
        public static function postData(string $table, array $data): array
        {
            return [
                'lastId' => 42,
                'comment' => 'The process was successful',
            ];
        }
    };

    $controller = new PosController(posModel: $posModel);

    ob_start();
    $controller->postData('users', ['name' => 'test']);
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json['status'])->toBe(200);
    expect($json['results']['lastId'])->toBe(42);
    expect($json['results']['comment'])->toBe('The process was successful');
});

test('postRegister hashes password and inserts when password is provided', function () {
    $posModel = new class extends PosModel {
        public static function postData(string $table, array $data): array
        {
            expect($data['password_user'])->not->toBe('plain123');

            return [
                'lastId' => 1,
                'comment' => 'The process was successful',
            ];
        }
    };

    $controller = new PosController(posModel: $posModel);

    ob_start();
    $controller->postRegister('users', ['password_user' => 'plain123'], 'user');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json['status'])->toBe(200);
    expect($json['results']['lastId'])->toBe(1);
});

test('postRegister without password does full registration flow', function () {
    $posModel = new class extends PosModel {
        public static function postData(string $table, array $data): array
        {
            return [
                'lastId' => 1,
                'comment' => 'The process was successful',
            ];
        }
    };

    $getModel = new class extends GetModel {
        public static function findWithFilters(
            string $table,
            string $select,
            string $linkTo,
            string $equalTo,
            ?string $orderBy,
            ?string $orderMode,
            ?int $startAt,
            ?int $endAt,
        ): array {
            $user = (object) [
                'id_user' => 1,
                'email_user' => $equalTo,
            ];

            return [$user];
        }
    };

    $putRepository = new class extends PutRepository {
        public function update(string $table, array $data, mixed $id, string $nameId): ?array
        {
            return ['comment' => 'The process was successful'];
        }
    };

    $jwtEncoder = fn (array $payload, string $key, string $alg) => 'fake.jwt.token';

    $controller = new PosController(
        posModel: $posModel,
        getModel: $getModel,
        putRepository: $putRepository,
        jwtEncoder: $jwtEncoder,
    );

    ob_start();
    $controller->postRegister('users', ['email_user' => 'a@b.com'], 'user');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json['status'])->toBe(200);
    expect($json['results'][0]['id_user'])->toBe(1);
    expect($json['results'][0]['email_user'])->toBe('a@b.com');
    expect($json['results'][0])->toHaveKey('token_user');
    expect($json['results'][0])->toHaveKey('token_exp_user');
    expect($json['results'][0]['token_user'])->toBe('fake.jwt.token');
});

test('postLogin returns 200 with token on valid credentials', function () {
    $password = 'correct_password';
    $crypted = crypt($password, '$2a$07$azybxcags23425sdg23sdfhsd$');

    $getModel = new class ($crypted) extends GetModel {
        public static string $storedPassword = '';

        public static function findWithFilters(
            string $table,
            string $select,
            string $linkTo,
            string $equalTo,
            ?string $orderBy,
            ?string $orderMode,
            ?int $startAt,
            ?int $endAt,
        ): array {
            $user = (object) [
                'id_user' => 1,
                'email_user' => $equalTo,
                'password_user' => self::$storedPassword,
            ];

            return [$user];
        }
    };
    $getModel::$storedPassword = $crypted;

    $putRepository = new class extends PutRepository {
        public function update(string $table, array $data, mixed $id, string $nameId): ?array
        {
            return ['comment' => 'The process was successful'];
        }
    };

    $jwtEncoder = fn (array $payload, string $key, string $alg) => 'fake.jwt.token';

    $controller = new PosController(
        getModel: $getModel,
        putRepository: $putRepository,
        jwtEncoder: $jwtEncoder,
    );

    ob_start();
    $controller->postLogin('users', ['email_user' => 'a@b.com', 'password_user' => $password], 'user');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json['status'])->toBe(200);
    expect($json['results'][0]['id_user'])->toBe(1);
    expect($json['results'][0]['email_user'])->toBe('a@b.com');
    expect($json['results'][0])->toHaveKey('token_user');
    expect($json['results'][0])->toHaveKey('token_exp_user');
    expect($json['results'][0]['token_user'])->toBe('fake.jwt.token');
});

test('postLogin returns wrong password error when password does not match', function () {
    $crypted = crypt('correct_password', '$2a$07$azybxcags23425sdg23sdfhsd$');

    $getModel = new class extends GetModel {
        public static string $storedPassword = '';

        public static function findWithFilters(
            string $table,
            string $select,
            string $linkTo,
            string $equalTo,
            ?string $orderBy,
            ?string $orderMode,
            ?int $startAt,
            ?int $endAt,
        ): array {
            $user = (object) [
                'id_user' => 1,
                'email_user' => $equalTo,
                'password_user' => self::$storedPassword,
            ];

            return [$user];
        }
    };
    $getModel::$storedPassword = $crypted;

    $controller = new PosController(getModel: $getModel);

    ob_start();
    $controller->postLogin('users', ['email_user' => 'a@b.com', 'password_user' => 'wrong_password'], 'user');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 400,
        'results' => 'Wrong password',
    ]);
});

test('postLogin returns wrong email error when user not found', function () {
    $getModel = new class extends GetModel {
        public static function findWithFilters(
            string $table,
            string $select,
            string $linkTo,
            string $equalTo,
            ?string $orderBy,
            ?string $orderMode,
            ?int $startAt,
            ?int $endAt,
        ): array {
            return [];
        }
    };

    $controller = new PosController(getModel: $getModel);

    ob_start();
    $controller->postLogin('users', ['email_user' => 'notfound@b.com'], 'user');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 400,
        'results' => 'Wrong email',
    ]);
});

test('postLogin with null password generates token without password check', function () {
    $getModel = new class extends GetModel {
        public static function findWithFilters(
            string $table,
            string $select,
            string $linkTo,
            string $equalTo,
            ?string $orderBy,
            ?string $orderMode,
            ?int $startAt,
            ?int $endAt,
        ): array {
            $user = (object) [
                'id_user' => 1,
                'email_user' => $equalTo,
                'password_user' => null,
            ];

            return [$user];
        }
    };

    $putRepository = new class extends PutRepository {
        public function update(string $table, array $data, mixed $id, string $nameId): ?array
        {
            return ['comment' => 'The process was successful'];
        }
    };

    $jwtEncoder = fn (array $payload, string $key, string $alg) => 'fake.jwt.token';

    $controller = new PosController(
        getModel: $getModel,
        putRepository: $putRepository,
        jwtEncoder: $jwtEncoder,
    );

    ob_start();
    $controller->postLogin('users', ['email_user' => 'a@b.com'], 'user');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json['status'])->toBe(200);
    expect($json['results'][0]['id_user'])->toBe(1);
    expect($json['results'][0])->toHaveKey('token_user');
    expect($json['results'][0])->toHaveKey('token_exp_user');
    expect($json['results'][0]['token_user'])->toBe('fake.jwt.token');
});
