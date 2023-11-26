<?php


namespace Feodorpranju\Eloquent\Bitrix24\Contracts;


use Feodorpranju\Eloquent\Bitrix24\Core\Authorization\Webhook;

interface Client
{
    /**
     * Client constructor.
     * @param string|Token $token if string, makes webhook token instance
     * @see Webhook
     */
    public function __construct(string|Token $token);

    public function call(string $action, array $data): array;

    public function getToken(): Token;

    public function getScope(string $collection): Scope;
}