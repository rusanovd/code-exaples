<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Exceptions;

use More\Exception\MoreException;

class IntercomApiException extends MoreException
{
    protected $message = 'Intercom api request error';
}
