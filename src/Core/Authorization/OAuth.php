<?php


namespace Feodorpranju\Eloquent\Bitrix24\Core\Authorization;

class OAuth extends AbstractToken
{
    public function __construct(string $url, string $authToken, string $refreshToken)
    {
        parent::__construct($url);
    }
}