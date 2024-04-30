<?php

namespace Pranju\Bitrix24\Models\Crm;

use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;

trait HasEntityTypeIdAttribute
{
    /**
     * Retrieves entity's entityTypeId (same as dynamicId)
     *
     * @return int
     * @throws Bitrix24Exception
     */
    public function getEntityTypeIdAttribute(): int
    {
        $repository = $this->getConnection()->getRepository($this->getTable());

        if ($repository instanceof HasDynamicId) {
            return $repository->getDynamicId();
        }

        return throw new Bitrix24Exception("'{$this->getTable()}' entity has no entity type");
    }
}