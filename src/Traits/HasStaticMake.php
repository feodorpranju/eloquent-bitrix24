<?php


namespace Pranju\Bitrix24\Traits;


trait HasStaticMake
{
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}