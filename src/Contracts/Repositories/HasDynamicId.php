<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

interface HasDynamicId
{
    /**
     * Retrieves table's dynamic id
     *
     * @return int
     */
    public function getDynamicId(): int;
}