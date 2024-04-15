<?php

namespace Pranju\Bitrix24\Factories;

use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Str;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse as BatchResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\ListResponse as ListResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\Response as ResponseInterface;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Core\Responses\ListResponse;
use Pranju\Bitrix24\Core\Responses\Response;

class ResponseFactory
{
    public static function make(HttpResponse $response, ?Command $command): ResponseInterface|ListResponseInterface|BatchResponseInterface
    {
        $method = $command->getMethod();

        if ($method === 'batch') {
            return new BatchResponse($response, $command);
        }

        if (Str::endsWith($method, 'list') || $response->json('total', false)) {
            return new ListResponse($response, $command);
        }

        return new Response($response, $command);
    }
}