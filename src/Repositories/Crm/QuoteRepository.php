<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;
use Pranju\Bitrix24\Enums\Crm\EntityTypeIdEnum;

class QuoteRepository extends AbstractCrmRepository implements HasDynamicId
{
    /**
     * @inheritDoc
     */
    public function getDynamicId(): int
    {
        return EntityTypeIdEnum::LEAD->value;
    }
}