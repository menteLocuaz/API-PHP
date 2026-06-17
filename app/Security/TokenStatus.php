<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Security;

enum TokenStatus: string
{
    case VALID = 'ok';
    case EXPIRED = 'expired';
    case INVALID = 'no-auth';
}
