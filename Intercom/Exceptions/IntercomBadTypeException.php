<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Exceptions;

use More\Exception\MoreException;

class IntercomBadTypeException extends MoreException
{
    protected $message = 'Intercom bad system type';
}
