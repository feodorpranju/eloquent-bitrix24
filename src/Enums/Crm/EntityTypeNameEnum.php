<?php

namespace Pranju\Bitrix24\Enums\Crm;

enum EntityTypeNameEnum: string
{
    case LEAD = 'LEAD';

    case DEAL = 'DEAL';

    case CONTACT = 'CONTACT';

    case COMPANY = 'COMPANY';

    case INVOICE = 'INVOICE';

    case SMART_INVOICE = 'SMART_INVOICE';

    case QUOTE = 'QUOTE';

    case REQUISITE = 'REQUISITE';
}
