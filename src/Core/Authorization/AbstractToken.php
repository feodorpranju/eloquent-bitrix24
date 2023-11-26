<?php


namespace Feodorpranju\Eloquent\Bitrix24\Core\Authorization;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Token;

abstract class AbstractToken implements Token
{
    protected string $url;

    public function __construct(string $url)
    {
        $this->setUrl($url);
    }

    /**
     * Sets bitrix24 url
     *
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = trim($url, '/').'/';
    }

    /**
     * Returns bitrix24 url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Gets subdomain for bitrix24 urls
     *
     * @return string
     */
    public function getSubdomain(): string
    {
        preg_match('/\/([a-zA-z0-9-]+)./', $this->getUrl(), $matches);

        return $matches[1] ?? 'unknown';
    }
}