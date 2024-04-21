<?php


namespace Pranju\Bitrix24\Traits;

use Pranju\Bitrix24\Contracts\Client;
use Illuminate\Support\Facades\DB;

trait GetsDefaultClient
{
    private static string $defaultConnection = 'bitrix24';

    /**
     * Gets default client
     *
     * @return Client|null
     */
    protected function getDefaultClient(): ?Client
    {
        return new \Pranju\Bitrix24\Core\Client('');
        $connection = config('database.defaultB24', static::$defaultConnection);

        if (
            ($connection = DB::connection($connection))
            && method_exists($connection, 'getClient')
        ) {
            $client = $connection->getClient();

            if ($client instanceof Client) {
                return $client;
            }
        }

        return null;
    }
}