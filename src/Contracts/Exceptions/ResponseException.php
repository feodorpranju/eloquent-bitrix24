<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Exceptions;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\Response;
use Throwable;

interface ResponseException extends Throwable
{
    public function __construct(Response $response);
}