<?php

namespace Pranju\Bitrix24\Eloquent\Factories;

use Illuminate\Support\Str;

abstract class Factory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    public static $namespace = __NAMESPACE__.'\\';

    protected static function appNamespace(): string
    {
        return 'Pranju\\Bitrix24\\';
    }
}