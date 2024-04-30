<?php

namespace Pranju\Bitrix24\Repositories\Crm;

class LeadContactRepository extends AbstractContactPivotRepository
{
    /**
     * @inheritDoc
     */
    protected const PARENT_ID_FIELD = 'LEAD_ID';

    /**
     * @inheritDoc
     */
    protected const CHILD_ID_FIELD = 'CONTACT_ID';
}