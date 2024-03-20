<?php

namespace Pranju\Bitrix24\Core\Responses;

use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\QueryException;

class ResponseException extends Bitrix24Exception
{
    public function __construct(protected Response $response)
    {
        parent::__construct();
        $this->code = $response->httpResponse()->status();
        $this->message = $this->response->httpResponse()->json('error', 'Undefined error');
    }

    public function toQueryException(): QueryException
    {
        return new QueryException(
            $this->response->command()
        );
    }
}