<?php

namespace Pranju\Bitrix24\Models\Crm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Pranju\Bitrix24\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $connection = 'bitrix24';

    protected $table = 'crm_lead';
}