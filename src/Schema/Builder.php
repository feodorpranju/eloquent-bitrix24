<?php

namespace Pranju\Bitrix24\Schema;

use Closure;

use function count;
use function current;
use function iterator_to_array;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /** @inheritdoc */
    public function hasColumn($table, $column)
    {
        return true;
    }

    /** @inheritdoc */
    public function hasColumns($table, array $columns)
    {
        return true;
    }

    /**
     * Determine if the given collection exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCollection($name)
    {
        $db = $this->connection->getMongoDB();

        $collections = iterator_to_array($db->listCollections([
            'filter' => ['name' => $name],
        ]), false);

        return count($collections) !== 0;
    }

    /** @inheritdoc */
    public function hasTable($collection)
    {
        return $this->hasCollection($collection);
    }

    /**
     * Modify a collection on the schema.
     *
     * @param string $collection
     *
     * @return void
     */
    public function collection($collection, Closure $callback)
    {
        $blueprint = $this->createBlueprint($collection);

        if ($callback) {
            $callback($blueprint);
        }
    }

    /** @inheritdoc */
    public function table($collection, Closure $callback)
    {
        $this->collection($collection, $callback);
    }

    /** @inheritdoc */
    public function create($collection, ?Closure $callback = null, array $options = [])
    {
        $blueprint = $this->createBlueprint($collection);

        $blueprint->create($options);

        if ($callback) {
            $callback($blueprint);
        }
    }

    /** @inheritdoc */
    public function dropIfExists($collection)
    {
        if ($this->hasCollection($collection)) {
            $this->drop($collection);
        }
    }

    /** @inheritdoc */
    public function drop($collection)
    {
        $blueprint = $this->createBlueprint($collection);

        $blueprint->drop();
    }

    /** @inheritdoc */
    public function dropAllTables()
    {
        foreach ($this->getAllCollections() as $collection) {
            $this->drop($collection);
        }
    }

    /** @inheritdoc */
    protected function createBlueprint($collection, ?Closure $callback = null)
    {
        return new Blueprint($this->connection, $collection);
    }

    /**
     * Get collection.
     *
     * @param string $name
     *
     * @return array|false
     */
    public function getCollection($name): array|false
    {
        return [];
    }

    /**
     * Get all of the collections names for bitrix24 connection.
     *
     * @return array
     */
    protected function getAllCollections(): array
    {
        return [];
    }
}
