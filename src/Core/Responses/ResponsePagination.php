<?php

namespace Feodorpranju\Eloquent\Bitrix24\Core\Responses;


use \Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\ResponsePagination as ResponsePaginationInterface;

class ResponsePagination implements ResponsePaginationInterface
{

    /**
     * @inheritDoc
     */
    public function __construct(protected int $next, protected int $total)
    {
    }

    /**
     * @inheritDoc
     */
    public function getNext(): int
    {
        return $this->next;
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}