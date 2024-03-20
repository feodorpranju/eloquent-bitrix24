<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Client;

interface Repository
{
    public function __construct(Client $client);

    /**
     * Retrieves repository's client
     *
     * @return Client
     */
    public function getClient(): Client;
}