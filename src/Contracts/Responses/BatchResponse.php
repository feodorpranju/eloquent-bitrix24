<?php

namespace Pranju\Bitrix24\Contracts\Responses;

interface BatchResponse extends Response
{
    /**
     * Gets array of separated responses
     *
     * @return Response[]|ListResponse[]|BatchResponse[]
     */
    public function responses(): array;
}