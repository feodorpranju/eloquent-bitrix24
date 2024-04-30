<?php

namespace Pranju\Bitrix24\Enums\Crm;

enum EntityTypeShortcodeEnum: string
{
    case LEAD = 'L';

    case DEAL = 'D';

    case CONTACT = 'C';

    case COMPANY = 'CO';

    case INVOICE = 'I';

    case SMART_INVOICE = 'SI';

    case QUOTE = 'Q';

    case REQUISITE = 'RQ';
}
