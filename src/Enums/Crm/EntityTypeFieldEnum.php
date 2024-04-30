<?php

namespace Pranju\Bitrix24\Enums\Crm;

enum EntityTypeFieldEnum: string
{
    case LEAD = 'CRM_LEAD';

    case DEAL = 'CRM_DEAL';

    case CONTACT = 'CRM_CONTACT';

    case COMPANY = 'CRM_COMPANY';

    case INVOICE = 'CRM_INVOICE';

    case SMART_INVOICE = 'CRM_SMART_INVOICE';

    case QUOTE = 'CRM_QUOTE';

    case REQUISITE = 'CRM_REQUISITE';
}
