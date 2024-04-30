<?php

namespace Pranju\Bitrix24\Models\Crm;

use Pranju\Bitrix24\Eloquent\Model;

class Requisite extends Model
{
    use HasEntityTypeIdAttribute;

    protected $table = 'crm_requisite';
}