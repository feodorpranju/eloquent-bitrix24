<?php

namespace Pranju\Bitrix24\Core\Responses;

use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Arr;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response as ResponseInterface;

class UnlimitedBatchResponse extends BatchResponse
{
    protected array $responses;

    public function __construct(protected array|HttpResponse $response, protected ?Command $command = null)
    {
        if (
            is_array($response)
            && (
                empty($response)
                || Arr::first($response) instanceof ResponseInterface
            )
        ) {
            $this->responses = $response;
        } else {
            parent::__construct($response, $command);
        }
    }
}