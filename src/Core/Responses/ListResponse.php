<?php

namespace Feodorpranju\Eloquent\Bitrix24\Core\Responses;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\ListResponse as ListResponseInterface;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\ResponsePagination as ResponsePaginationInterface;

class ListResponse extends Response implements ListResponseInterface
{
    protected ResponsePaginationInterface $responsePagination;

    /**
     * @inheritDoc
     */
    public function pagination(): ResponsePaginationInterface
    {
        return $this->responsePagination ??= new ResponsePagination(
            $this['next'] ?? 0,
            $this['next'] ?? 0,
        );
    }
}