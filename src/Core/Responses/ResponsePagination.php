<?php

namespace Pranju\Bitrix24\Core\Responses;


use \Pranju\Bitrix24\Contracts\Responses\ResponsePagination as ResponsePaginationInterface;

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
    public function next(): int
    {
        return $this->next;
    }

    /**
     * @inheritDoc
     */
    public function total(): int
    {
        return $this->total;
    }
}