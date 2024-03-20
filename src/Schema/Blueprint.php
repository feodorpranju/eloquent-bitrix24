<?php

namespace Pranju\Bitrix24\Schema;

use Pranju\Bitrix24\Connection;

use Illuminate\Database\Schema\ColumnDefinition;
use function array_flip;
use function implode;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function key;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * The MongoCollection object for this blueprint.
     *
     * @var Collection
     */
    protected Collection $collection;

    /**
     * Fluent columns.
     *
     * @var ColumnDefinition[]
     */
    protected $columns = [];

    /**
     * Create a new schema blueprint.
     * @param Connection $connection
     * @param string|Collection $collection
     */
    public function __construct(protected Connection $connection, string|Collection $collection)
    {
        $this->collection = is_string($collection)
            ? $this->connection->getCollection($collection)
            : $collection;
    }

    /** @inheritdoc */
    public function index($columns = null, $name = null, $algorithm = null, $options = []): static
    {
        return $this;
    }

    /** @inheritdoc */
    public function primary($columns = null, $name = null, $algorithm = null, $options = []): static
    {
        return $this->unique($columns, $name, $algorithm, $options);
    }

    /** @inheritdoc */
    public function dropIndex($index): static
    {
        return $this;
    }

    /**
     * @param string|array $indexOrColumns
     *
     * @return string
     */
    protected function transformColumns(array|string $indexOrColumns): string
    {
        if (is_array($indexOrColumns)) {
            $indexOrColumns = $this->fluent($indexOrColumns);

            // Transform the columns to the index name.
            $transform = [];

            foreach ($indexOrColumns as $key => $value) {
                if (is_int($key)) {
                    // There is no sorting order, use the default.
                    $column  = $value;
                    $sorting = '1';
                } else {
                    // This is a column with sorting order e.g 'my_column' => -1.
                    $column  = $key;
                    $sorting = $value;
                }

                $transform[$column] = $column . '_' . $sorting;
            }

            $indexOrColumns = implode('_', $transform);
        }

        return $indexOrColumns;
    }

    /** @inheritdoc */
    public function unique($columns = null, $name = null, $algorithm = null, $options = []): static
    {
        $columns = $this->fluent($columns);

        $options['unique'] = true;

        $this->index($columns, $name, $algorithm, $options);

        return $this;
    }

    /** @inheritdoc */
    public function create(): void
    {

    }

    /** @inheritdoc */
    public function drop(): void
    {

    }

    /** @inheritdoc */
    public function addColumn($type, $name, array $parameters = []): static
    {
        $this->fluent($name);

        return $this;
    }

    /**
     * Specify a sparse and unique index for the collection.
     *
     * @param string|array $columns
     * @param array        $options
     *
     * @return Blueprint
     *
     * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
     */
    public function sparse_and_unique($columns = null, $options = [])
    {
        $columns = $this->fluent($columns);

        $options['sparse'] = true;
        $options['unique'] = true;

        $this->index($columns, null, null, $options);

        return $this;
    }

    /**
     * Allow fluent columns.
     *
     * @param string|array $columns
     *
     * @return string|array
     */
    protected function fluent($columns = null)
    {
        if ($columns === null) {
            return $this->columns;
        }

        if (is_string($columns)) {
            return $this->columns = [$columns];
        }

        return $this->columns = $columns;
    }

    /**
     * Allows the use of unsupported schema methods.
     *
     * @param string $method
     * @param array  $args
     *
     * @return Blueprint
     */
    public function __call($method, $args)
    {
        // Dummy.
        return $this;
    }
}
