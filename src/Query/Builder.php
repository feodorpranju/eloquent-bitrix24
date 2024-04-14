<?php

namespace Pranju\Bitrix24\Query;

use BadMethodCallException;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Pranju\Bitrix24\Connection;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\Repository;
use Pranju\Bitrix24\Traits\Dumps;
use RuntimeException;

class Builder extends BaseBuilder implements Arrayable
{
    use Dumps;

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
        $results = $this->processor->processSelect(
            $this,
            $this->getRepository()->getSelectedItems(
                $this->grammar->compileSelect($this)->call()
            ),
        );

        return collect($results);
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

    public function insert(array $values)
    {
        $this->applyBeforeQueryCallbacks();

        return $this->grammar->compileInsert($this, $values)->call()->successful();
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
    public function exists(): bool
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

    /**
     * Retrieves select command as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->toCmd()->toArray();
    }

    /**
     * Retrieves select command
     *
     * @return Command
     */
    public function toCmd(): Command
    {
        return $this->grammar->compileSelect($this);
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
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereColumn');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false): void
    {
        $this->throwMethodNotSupported('whereIntegerInRaw');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereIntegerInRaw($column, $values): void
    {
        $this->throwMethodNotSupported('whereIntegerInRaw');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereIntegerNotInRaw($column, $values, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereIntegerNotInRaw');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereIntegerNotInRaw($column, $values, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereIntegerNotInRaw');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereBetweenColumns($column, array $values, $boolean = 'and', $not = false): void
    {
        $this->throwMethodNotSupported('whereBetweenColumns');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereDay($column, $operator, $value = null, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereDay');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereExists($callback, $boolean = 'and', $not = false): void
    {
        $this->throwMethodNotSupported('whereExists');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereJsonContains($column, $value, $boolean = 'and', $not = false): void
    {
        $this->throwMethodNotSupported('whereExists');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereJsonContainsKey($column, $boolean = 'and', $not = false): void
    {
        $this->throwMethodNotSupported('whereJsonContainsKey');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereJsonDoesntContain($column, $value, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereJsonDoesntContain');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereJsonDoesntContainKey($column, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereJsonDoesntContainKey');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereJsonLength($column, $operator, $value = null, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereJsonLength');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereMonth');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereNotBetweenColumns($column, array $values, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereNotBetweenColumns');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereNotExists($callback, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereNotExists');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereRaw($sql, $bindings = [], $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereRaw');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereRowValues($columns, $operator, $values, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereRowValues');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function whereTime($column, $operator, $value = null, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('whereTime');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereColumn($first, $operator = null, $second = null): void
    {
        $this->throwMethodNotSupported('whereColumn');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereBetween($column, iterable $values): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereBetweenColumns($column, array $values): void
    {
        $this->throwMethodNotSupported('whereBetweenColumns');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereDate($column, $operator, $value = null): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereDay($column, $operator, $value = null): void
    {
        $this->throwMethodNotSupported('whereDay');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhere($column, $operator = null, $value = null)
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereExists($callback, $not = false): void
    {
        $this->throwMethodNotSupported('whereExists');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereFullText($columns, $value, array $options = []): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereIn($column, $values): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereJsonContains($column, $value): void
    {
        $this->throwMethodNotSupported('whereJsonContains');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereJsonContainsKey($column): void
    {
        $this->throwMethodNotSupported('whereJsonContainsKey');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereJsonDoesntContain($column, $value): void
    {
        $this->throwMethodNotSupported('whereJsonDoesntContain');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereJsonDoesntContainKey($column): void
    {
        $this->throwMethodNotSupported('whereJsonDoesntContainKey');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereJsonLength($column, $operator, $value = null): void
    {
        $this->throwMethodNotSupported('whereJsonLength');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereNot($column, $operator = null, $value = null): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereMonth($column, $operator, $value = null): void
    {
        $this->throwMethodNotSupported('whereMonth');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereNotBetween($column, iterable $values): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereNotBetweenColumns($column, array $values): void
    {
        $this->throwMethodNotSupported('whereNotBetweenColumns');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereNotExists($callback): void
    {
        $this->throwMethodNotSupported('whereNotExists');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereNotIn($column, $values): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereNotNull($column): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereNull($column): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereRaw($sql, $bindings = []): void
    {
        $this->throwMethodNotSupported('whereRaw');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereTime($column, $operator, $value = null): void
    {
        $this->throwMethodNotSupported('whereTime');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orWhereYear($column, $operator, $value = null): void
    {
        $this->throwLogicNotSupported();
    }

    /** @internal This method is not supported by Bitrix24. */
    public function groupByRaw($sql, array $bindings = []): void
    {
        $this->throwMethodNotSupported('group');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orderByRaw($sql, $bindings = []): void
    {
        $this->throwMethodNotSupported('orderByRaw');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function unionAll($query): void
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function union($query, $all = false): void
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function setUnions(array $unions): void
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function getUnionLimit(): int
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function getUnionOffset(): int
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function getUnionOrders(): array
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function getUnions(): array
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function setUnionLimit(int $unionLimit): void
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function setUnionOffset(int $unionOffset): void
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function setUnionOrders(array $unionOrders): void
    {
        $this->throwMethodNotSupported('union');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function having($column, $operator = null, $value = null, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function havingBetween($column, iterable $values, $boolean = 'and', $not = false): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function havingNested(Closure $callback, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function havingNotNull($columns, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function havingNull($columns, $boolean = 'and', $not = false): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orHaving($column, $operator = null, $value = null)
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function addNestedHavingQuery($query, $boolean = 'and'): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orHavingNotNull($column): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orHavingNull($column): void
    {
        $this->throwMethodNotSupported('having');
    }

    /** @internal This method is not supported by Bitrix24. */
    public function orHavingRaw($sql, array $bindings = []): void
    {
        $this->throwMethodNotSupported('having');
    }

    /**
     * Throws Method not supported exception
     *
     * @param string $method
     * @return void
     */
    private function throwMethodNotSupported(string $method): void
    {
        throw new BadMethodCallException("Method '$method' is not supported by this version of Bitrix24 Eloquent package");
    }

    /**
     * Throws logic is not supported exception
     *
     * @return void
     */
    private function throwLogicNotSupported(): void
    {
        throw new BadMethodCallException('Logic is not supported by this version of Bitrix24 Eloquent package');
    }
}
