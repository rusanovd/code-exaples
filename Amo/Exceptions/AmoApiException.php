<?php

declare(strict_types=1);

namespace More\Amo\Exceptions;

use More\Exception\MoreException;

class AmoApiException extends MoreException
{
    protected $message = 'Amo api request error';
}
