<?php

namespace Pranju\Bitrix24\Repositories\Crm;

class CompanyContactRepository extends AbstractContactPivotRepository
{
    /**
     * @inheritDoc
     */
    protected const PARENT_ID_FIELD = 'COMPANY_ID';

    /**
     * @inheritDoc
     */
    protected const CHILD_ID_FIELD = 'CONTACT_ID';
}