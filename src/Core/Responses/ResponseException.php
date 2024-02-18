<?php

namespace Feodorpranju\Eloquent\Bitrix24\Core\Responses;

use Exception;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\Response;
use \Feodorpranju\Eloquent\Bitrix24\Contracts\Exceptions\ResponseException as ResponseExceptionInterface;

class ResponseException extends Exception implements ResponseExceptionInterface
{
    public function __construct(protected Response $response)
    {
        parent::__construct();
        $this->code = $response->httpResponse()->status();
        $this->message = $this->response->httpResponse()->json('error', 'Undefined error');
    }
}