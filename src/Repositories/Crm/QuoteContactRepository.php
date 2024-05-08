<?php

namespace Pranju\Bitrix24\Repositories\Crm;

class QuoteContactRepository extends AbstractContactPivotRepository
{
    /**
     * @inheritDoc
     */
    protected const PARENT_ID_FIELD = 'QUOTE_ID';

    /**
     * @inheritDoc
     */
    protected const CHILD_ID_FIELD = 'CONTACT_ID';
}