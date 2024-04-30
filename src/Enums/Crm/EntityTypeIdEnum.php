<?php

namespace Pranju\Bitrix24\Enums\Crm;

enum EntityTypeIdEnum: int
{
    case LEAD = 1;

    case DEAL = 2;

    case CONTACT = 3;

    case COMPANY = 4;

    case INVOICE = 5;

    case SMART_INVOICE = 31;

    case QUOTE = 7;

    case REQUISITE = 8;
}
