<?php

namespace Pranju\Bitrix24\Models\Crm\Timeline;

use Pranju\Bitrix24\Eloquent\Model;
use Pranju\Bitrix24\Models\Crm\HasEntityTypeIdAttribute;

class Logmessage extends Model
{
    use HasEntityTypeIdAttribute;

    protected $table = 'crm_timeline_logmessage';
}