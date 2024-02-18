<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Exceptions;

use Throwable;

interface BaseException extends Throwable
{
    public function __construct(mixed $context, int $code = 0, ?Throwable $previous = null);
}