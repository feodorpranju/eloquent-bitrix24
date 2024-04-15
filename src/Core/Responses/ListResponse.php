<?php

namespace Pranju\Bitrix24\Core\Responses;

use Pranju\Bitrix24\Contracts\Responses\ListResponse as ListResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\ResponsePagination as ResponsePaginationInterface;

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
            $this['total'] ?? 0,
        );
    }
}