<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;

class CompanyRepository extends AbstractCrmRepository implements HasDynamicId
{
    /**
     * @inheritDoc
     */
    public function getDynamicId(): int
    {
        return 4;
    }
}