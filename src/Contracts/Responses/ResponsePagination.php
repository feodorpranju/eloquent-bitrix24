<?php

namespace Pranju\Bitrix24\Contracts\Responses;

interface ResponsePagination
{
    /**
     * Creates new instance
     *
     * @param int $next
     * @param int $total
     */
    public function __construct(int $next, int $total);

    /**
     * Returns offset for next list command
     *
     * @return int
     */
    public function next(): int;

    /**
     * Returns total number of items for filter in command
     *
     * @return int
     */
    public function total(): int;
}