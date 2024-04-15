<?php

namespace Pranju\Bitrix24\Factories;

use Illuminate\Support\Str;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Client;
use Pranju\Bitrix24\Contracts\Repositories\Repository;

class RepositoryFactory
{
    /**
     * Repositories cache
     *
     * @var Repository[]
     */
    private array $cache = [];

    public function __construct(private readonly Client $client)
    {
        //
    }

    /**
     * Resolves repository instance.
     * Caches it in instance
     *
     * @param string $table
     * @return Repository
     * @throws Bitrix24Exception
     */
    public function make(string $table): Repository
    {
        if (isset($this->cache[$table])) {
            return $this->cache[$table];
        }

        $scope = Str::studly(Str::before($table, '_'));
        $repository = Str::studly(Str::after($table, '_')).'Repository';
        $class = Str::beforeLast(__NAMESPACE__, '\\')."\\Repositories\\$scope\\$repository";

        if (!class_exists($class)) {
            throw new Bitrix24Exception("Undefined repository '$class' for '$table' table");
        }

        return $this->cache[$table] = new $class($this->client);
    }
}