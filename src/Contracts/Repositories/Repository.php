<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Client;

interface Repository
{
    public function __construct(Client $client, string $table);

    /**
     * Retrieves repository's client
     *
     * @return Client
     */
    public function getClient(): Client;

    /**
     * Retrieves table's primary key
     *
     * @return string
     */
    public function getPrimaryKey(): string;
}