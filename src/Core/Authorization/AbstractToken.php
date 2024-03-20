<?php


namespace Pranju\Bitrix24\Core\Authorization;


use Pranju\Bitrix24\Contracts\Token;
use Illuminate\Support\Str;

abstract class AbstractToken implements Token
{
    /**
     * API URL
     *
     * @var string
     */
    protected string $url;

    /**
     * API URL host
     *
     * @var string
     */
    protected string $host;

    public function __construct(string $url)
    {
        $this->setUrl($url);
    }

    /**
     * @inheritDoc
     */
    public function setUrl(string $url): void
    {
        $this->url = trim($url, '/').'/';
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function getSubdomain(): string
    {
        return Str::before($this->getHost(), '.');
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host ??= parse_url($this->getUrl(), PHP_URL_HOST);
    }
}