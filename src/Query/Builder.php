<?php

namespace Pranju\Bitrix24\Query;

use ArgumentCountError;
use BadMethodCallException;
use Carbon\CarbonPeriod;
use Closure;
use DateTimeInterface;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Connection;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use LogicException;
use Pranju\Bitrix24\Contracts\Repositories\Repository;
use RuntimeException;

class Builder extends BaseBuilder
{
    private const REGEX_DELIMITERS = ['/', '#', '~'];
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * The database collection.
     *
     * @var string
     */
    protected string $table;

    /**
     * The column projections.
     *
     * @var array
     */
    public array $projections;

    /**
     * The cursor timeout value.
     *
     * @var int
     */
    public int $timeout;

    /**
     * The cursor hint value.
     *
     * @var int
     */
    public int $hint;

    /**
     * Custom options to add to the query.
     *
     * @var array
     */
    public array $options = [];

    /**
     * The database connection instance.
     *
     * @var Connection
     */
    public $connection;

    /**
     * All the available clause operators.
     *
     * @var array
     */
    public $operators = [
        '',
        '=',
        '<',
        '>',
        '<=',
        '>=',
        '<>',
        '!=',
        '!',
        'like',
        'not like',
        'between',
        'ilike',
    ];

    /**
     * Operator conversion.
     *
     * @var array
     */
    protected array $conversion = [
        'like' => '',
        'ilike' => '',
        'not like' => '!',
    ];


    /**
     * @inheritdoc
     */
    public $bindings = [
        'select' => [],
        'from' => [],
        'where' => [],
        'groupBy' => [],
        'order' => [],
    ];

    /**
     * Set the projections.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function project(array $columns): static
    {
        $this->projections = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Set the cursor timeout in seconds.
     *
     * @param int $seconds
     *
     * @return $this
     */
    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Retrieves repository for selected table
     *
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return $this->connection->getRepository($this->from);
    }

    /** @inheritdoc */
    public function find($id, $columns = [])
    {
        $command = $this->grammar->compileFind($this, $id);

        return $this->processor->processFind($this, $command, $columns);
    }

    /** @inheritdoc */
    public function value($column)
    {
        $result = (array) $this->first([$column]);

        return Arr::get($result, $column);
    }

    /** @inheritdoc */
    public function get($columns = [])
    {
        $wheres = $this->grammar->compileWheres($this);

        dd($wheres);
    }

    /** @inheritdoc */
    public function cursor($columns = [])
    {
        $result = $this->getFresh($columns, true);
        if ($result instanceof LazyCollection) {
            return $result;
        }

        throw new RuntimeException('Query not compatible with cursor');
    }

    /**
     * Return the Bitrix24 query to be run in the form of an element array like ['method' => [arguments]].
     *
     * Example: ['find' => [['name' => 'John Doe'], ['projection' => ['birthday' => 1]]]]
     *
     * @return array<string, array>
     */
    public function toB24(): array
    {
        $columns = $this->columns ?? [];

        // Drop all columns if * is present, MongoDB does not work this way.
        if (in_array('*', $columns)) {
            $columns = [];
        }

        $wheres = $this->compileWheres();

        $options = [];

        if ($this->columns) {
            $options['select'] = $this->columns;
        }

        if ($this->orders) {
            $options['order'] = $this->orders;
        }

        if ($this->offset) {
            $options['start'] = $this->offset;
        }

        if ($this->limit) {
            $options['limit'] = $this->limit;
        }

        if (count($this->options)) {
            $options = array_merge($options, $this->options);
        }

        if ($wheres) {
            $options['filter'] = $wheres;
        }

        return $options;
    }

    /**
     * Execute the query as a fresh "select" statement.
     *
     * @param  array $columns
     * @param  bool  $returnLazy
     *
     * @return array|static[]|Collection|LazyCollection
     */
    public function getFresh($columns = [], $returnLazy = false)
    {
        dd($this->wheres);
        dd(collect(debug_backtrace())->pluck('line', 'file'));
        // If no columns have been specified for the select statement, we will set them
        // here to either the passed columns, or the standard default of retrieving
        // all of the columns on the table using the "wildcard" column character.
        if ($this->columns === null) {
            $this->columns = $columns;
        }

        // Drop all columns if * is present, MongoDB does not work this way.
        if (in_array('*', $this->columns)) {
            $this->columns = [];
        }

        $command = $this->toCmd();

        if ($returnLazy) {
            return LazyCollection::make(function () use ($result) {
                foreach ($result as $item) {
                    yield $item;
                }
            });
        }

        return new Collection($result);
    }

    /**
     * Gets ID value if it is set in wheres
     *
     * @return int|string|null
     */
    public function getIdFromWheres(): int|string|null
    {
        if (
            count($this->wheres) === 1
            && strtolower($this->wheres[0]['column']) === 'id'
            && $this->wheres[0]['operator'] === '='
        ) {
            return $this->wheres[0]['value'];
        }

        return null;
    }

    /**
     * Generate the unique cache key for the current query.
     *
     * @return string
     */
    public function generateCacheKey(): string
    {
        $key = [
            'connection' => $this->connection->getDatabaseName(),
            'table' => $this->table,
            'wheres' => $this->wheres,
            'columns' => $this->columns,
            'groups' => $this->groups,
            'orders' => $this->orders,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'aggregate' => $this->aggregate,
        ];

        return md5(serialize(array_values($key)));
    }

    /** @inheritdoc */
    public function exists()
    {
        $this->applyBeforeQueryCallbacks();

        return $this->first() !== null;
    }

    /** @inheritdoc */
    public function insertGetId(array $values, $sequence = null): int
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }

    /** @inheritdoc */
    public function update(array $values, array $options = [])
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->grammar->compileUpdate($this, $values);

        return $this->processor->processUpdate($this, $sql);
    }

    /** @inheritdoc */
    public function __call($method, $parameters)
    {
        if ($method === 'unset') {
            return $this->drop(...$parameters);
        }

        return parent::__call($method, $parameters);
    }

    /** @internal This method is not supported by Bitrix24. */
    public function toSql()
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24. Try "toCmd()" instead.');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function toRawSql()
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24. Try "toCmd()" instead.');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function groupByRaw($sql, array $bindings = [])
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orderByRaw($sql, $bindings = [])
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function unionAll($query)
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function union($query, $all = false)
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function havingBetween($column, iterable $values, $boolean = 'and', $not = false)
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereIntegerInRaw($column, $values)
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereIntegerNotInRaw($column, $values, $boolean = 'and')
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereIntegerNotInRaw($column, $values, $boolean = 'and')
    {
        throw new BadMethodCallException('This method is not supported by Bitrix24');
    }
}
