<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Client;

interface Repository
{
    public function __construct(Client $client);
}