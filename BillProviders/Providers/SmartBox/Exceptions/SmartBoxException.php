<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Exceptions;

use More\Exception\MoreException;

class SmartBoxException extends MoreException
{
    protected $message = 'SmartBox error';
}
