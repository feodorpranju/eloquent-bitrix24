<?php


namespace Pranju\Bitrix24\Contracts;


interface Token
{
    /**
     * Sets API URL.
     * Provide with token for webhook auth
     *
     * @param string $url
     * @return void
     */
    public function setUrl(string $url): void;

    /**
     * Retrieves API URL
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Retrieves host subdomain
     *
     * @return string
     */
    public function getSubdomain(): string;

    /**
     * Retrieves host e.g. example.bitrix24.ru
     *
     * @return string
     */
    public function getHost(): string;
}