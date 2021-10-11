<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Exceptions;

use More\Exception\MoreException;

class IntercomEmptyOptionsException extends MoreException
{
    protected $message = 'Intercom empty options';
}
