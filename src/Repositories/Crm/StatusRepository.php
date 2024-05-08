<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;
use Pranju\Bitrix24\Enums\Crm\EntityTypeIdEnum;

class StatusRepository extends AbstractCrmRepository
{
    /**
     * @inheritDoc
     */
    public function getAllColumnsSelect(): array
    {
        return [];
    }
}