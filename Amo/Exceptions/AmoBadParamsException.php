<?php

declare(strict_types=1);

namespace More\Amo\Exceptions;

use More\Exception\MoreException;

class AmoBadParamsException extends MoreException
{
    protected $message = 'Amo bad params';
}
