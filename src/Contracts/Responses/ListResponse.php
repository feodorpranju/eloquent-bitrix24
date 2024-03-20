<?php

namespace Pranju\Bitrix24\Contracts\Responses;

interface ListResponse extends Response
{
    /**
     * Gets array of separated responses
     *
     * @return ResponsePagination
     */
    public function pagination(): ResponsePagination;
}