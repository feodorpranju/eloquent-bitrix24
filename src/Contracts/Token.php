<?php


namespace Feodorpranju\Eloquent\Bitrix24\Contracts;


interface Token
{
    public function setUrl(string $url): void;

    public function getUrl(): string;

    public function getSubdomain(): string;
}