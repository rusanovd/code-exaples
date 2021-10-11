<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Exceptions;

use More\Exception\MoreException;

class SmartBoxBadParamsException extends MoreException
{
    protected $message = 'SmartBox bad params error';
}
