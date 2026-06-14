<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Database\Builders\JoinBuilder;
use Arancamon\ApiPhp\Database\Builders\RangeBuilder;
use Arancamon\ApiPhp\Database\Builders\SearchBuilder;
use Arancamon\ApiPhp\Database\Builders\SelectBuilder;
use Arancamon\ApiPhp\Database\Builders\WhereBuilder;
use Arancamon\ApiPhp\Database\Exceptions\QueryBuilderException;
use Arancamon\ApiPhp\Database\QueryBuilder;

// ─── SelectBuilder ───────────────────────────────────────────────────────────

test('buildSelect returns valid SELECT clause', function () {
    $sql = SelectBuilder::build('users', 'id,name');

    expect($sql)->toBe('SELECT id,name FROM users');
});

test('buildSelect validates identifiers', function () {
    SelectBuilder::build('users', 'id,name');
})->throwsNoExceptions();

test('buildSelect rejects invalid table', function () {
    SelectBuilder::build('users; DROP TABLE', '*');
})->throws(QueryBuilderException::class, 'Invalid SQL identifier');

test('buildOrder returns empty string when null', function () {
    expect(SelectBuilder::buildOrder(null, null))->toBe('');
});

test('buildOrder returns ORDER BY clause', function () {
    expect(SelectBuilder::buildOrder('name', 'ASC'))->toBe(' ORDER BY name ASC');
});

test('buildOrder rejects invalid mode', function () {
    SelectBuilder::buildOrder('name', 'invalid');
})->throws(QueryBuilderException::class, 'Invalid ORDER BY mode');

test('buildLimit returns empty string when null', function () {
    expect(SelectBuilder::buildLimit(null, null))->toBe('');
});

test('buildLimit returns LIMIT clause', function () {
    expect(SelectBuilder::buildLimit(0, 10))->toBe(' LIMIT 10 OFFSET 0');
});

// ─── WhereBuilder ────────────────────────────────────────────────────────────

test('buildWhere returns empty string for empty conditions', function () {
    expect(WhereBuilder::build([]))->toBe('');
});

test('buildWhere returns WHERE clause', function () {
    expect(WhereBuilder::build(['id = :id', 'name = :name']))
        ->toBe(' WHERE id = :id AND name = :name');
});

test('buildConditionsFromLinkTo parses single field', function () {
    $result = WhereBuilder::buildConditionsFromLinkTo('id');

    expect($result['conditions'])->toBe(['id = :id']);
    expect($result['fields'])->toBe(['id']);
});

test('buildConditionsFromLinkTo parses multiple fields', function () {
    $result = WhereBuilder::buildConditionsFromLinkTo('id,name');

    expect($result['conditions'])->toBe(['id = :id', 'name = :name']);
});

test('buildFiltersWithQualification adds table prefix', function () {
    $result = WhereBuilder::buildFiltersWithQualification('id', 'users');

    expect($result['conditions'])->toBe(['users.id = :users_id']);
    expect($result['fields'])->toBe(['users.id']);
});

test('buildFiltersWithQualification keeps explicit prefix', function () {
    $result = WhereBuilder::buildFiltersWithQualification('users.id', 'users');

    expect($result['conditions'])->toBe(['users.id = :users_id']);
});

// ─── JoinBuilder ─────────────────────────────────────────────────────────────

test('buildJoin returns empty for single table', function () {
    expect(JoinBuilder::build(['users'], ['user']))->toBe('');
});

test('buildJoin builds two-table join', function () {
    $sql = JoinBuilder::build(['users', 'posts'], ['user', 'post']);

    expect($sql)->toMatch('/INNER JOIN posts ON users\.id_user = posts\.id_user_post/');
});

test('buildJoin builds three-table join', function () {
    $sql = JoinBuilder::build(['users', 'posts', 'comments'], ['user', 'post', 'comment']);

    expect($sql)->toMatch('/INNER JOIN posts ON users\.id_user = posts\.id_user_post/');
    expect($sql)->toMatch('/INNER JOIN comments ON posts\.id_comment_post = comments\.id_comment/');
});

// ─── SearchBuilder ───────────────────────────────────────────────────────────

test('buildConditions returns ILIKE conditions', function () {
    $conditions = SearchBuilder::buildConditions('name,email');

    expect($conditions)->toBe([
        'CAST(name AS TEXT) ILIKE :search',
        'CAST(email AS TEXT) ILIKE :search',
    ]);
});

test('buildRelationsConditions parses single column', function () {
    $result = SearchBuilder::buildRelationsConditions('name');

    expect($result['firstColumn'])->toBe('name');
    expect($result['extraConditionSql'])->toBe('');
    expect($result['extraFields'])->toBe([]);
});

test('buildRelationsConditions parses multiple columns', function () {
    $result = SearchBuilder::buildRelationsConditions('name,status');

    expect($result['firstColumn'])->toBe('name');
    expect($result['extraConditionSql'])->toMatch('/AND status = :status/');
    expect($result['extraFields'])->toBe(['status']);
});

// ─── RangeBuilder ────────────────────────────────────────────────────────────

test('buildCondition returns BETWEEN clause', function () {
    expect(RangeBuilder::buildCondition('price'))
        ->toBe('price BETWEEN :between_from AND :between_to');
});

test('buildInFilter returns IN clause', function () {
    expect(RangeBuilder::buildInFilter('status', '1,2,3'))
        ->toBe('AND status IN (1,2,3)');
});

// ─── QueryBuilder Facade ─────────────────────────────────────────────────────

test('QueryBuilder facade delegates correctly', function () {
    $sql = QueryBuilder::buildClauses('users', 'id,name', 'name', 'DESC', 0, 10);

    expect($sql)->toBe('SELECT id,name FROM users ORDER BY name DESC LIMIT 10 OFFSET 0');
});

test('QueryBuilder facade without optional clauses', function () {
    $sql = QueryBuilder::buildClauses('users', '*');

    expect($sql)->toBe('SELECT * FROM users');
});

test('QueryBuilder validateIdentifier rejects dangerous input', function () {
    QueryBuilder::validateIdentifier('safe_name');
})->throwsNoExceptions();

test('QueryBuilder validateIdentifier rejects injection attempt', function () {
    QueryBuilder::validateIdentifier('id; DROP TABLE users');
})->throws(QueryBuilderException::class);

test('QueryBuilder validates identifier with dots and commas', function () {
    QueryBuilder::validateIdentifier('users.id,posts.title');
})->throwsNoExceptions();
