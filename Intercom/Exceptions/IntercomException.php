<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Exceptions;

use More\Exception\MoreException;

class IntercomException extends MoreException
{
    protected $message = 'Intercom error';
}
