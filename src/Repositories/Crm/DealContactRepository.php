<?php

namespace Pranju\Bitrix24\Repositories\Crm;

class DealContactRepository extends AbstractContactPivotRepository
{
    /**
     * @inheritDoc
     */
    protected const PARENT_ID_FIELD = 'DEAL_ID';

    /**
     * @inheritDoc
     */
    protected const CHILD_ID_FIELD = 'CONTACT_ID';
}