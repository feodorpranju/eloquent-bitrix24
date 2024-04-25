<?php

namespace Pranju\Bitrix24\Models\Crm;

use Pranju\Bitrix24\Eloquent\Model;

class Deal extends Model
{
    protected $connection = 'bitrix24';

    protected $table = 'crm_deal';
}