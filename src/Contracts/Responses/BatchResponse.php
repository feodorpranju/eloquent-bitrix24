<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Responses;

interface BatchResponse extends Response
{
    /**
     * Gets array of separated responses
     *
     * @return Response[]
     */
    public function responses(): array;
}