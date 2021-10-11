<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Exceptions;

use More\Exception\MoreException;

class IntercomConfigDisabledException extends MoreException
{
    protected $message = 'Intercom is disabled.';
}
