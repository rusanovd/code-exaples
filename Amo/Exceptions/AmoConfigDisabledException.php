<?php

declare(strict_types=1);

namespace More\Amo\Exceptions;

use More\Exception\MoreException;

class AmoConfigDisabledException extends MoreException
{
    protected $message = 'Amo is disabled.';
}
