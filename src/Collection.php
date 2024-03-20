<?php

namespace Pranju\Bitrix24;

use Exception;
use Pranju\Bitrix24\Contracts\Scope;

/**
 * Class Collection
 * @package Pranju\Bitrix24
 */
class Collection
{
    /**
     * Collection name.
     */
    protected string $collection;

    /**
     * @var Scope
     */
    protected Scope $scope;

    public function __construct(protected Connection $connection, string $collection)
    {
        $this->collection = IlluminateCollection::make($collection);
    }

    /**
     * Handle dynamic method calls.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        $start  = microtime(true);
        $result = $this->collection->$method(...$parameters);

        // Once we have run the query we will calculate the time that it took to run and
        // then log the query, bindings, and execution time so we will report them on
        // the event that the developer needs them. We'll log time in milliseconds.
        $time = $this->connection->getElapsedTime($start);

        $query = [];

        // Convert the query parameters to a json string.
        array_walk_recursive($parameters, function (&$item, $key) {
            if ($item instanceof ObjectID) {
                $item = (string) $item;
            }
        });

        // Convert the query parameters to a json string.
        foreach ($parameters as $parameter) {
            try {
                $query[] = json_encode($parameter, JSON_THROW_ON_ERROR);
            } catch (Exception) {
                $query[] = '{...}';
            }
        }

        $queryString = $this->collection->getCollectionName() . '.' . $method . '(' . implode(',', $query) . ')';

        $this->connection->logQuery($queryString, [], $time);

        return $result;
    }
}
