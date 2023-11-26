<?php

namespace Feodorpranju\Eloquent\Bitrix24\Query;

use ArgumentCountError;
use BadMethodCallException;
use Carbon\CarbonPeriod;
use Closure;
use DateTimeInterface;
use Feodorpranju\Eloquent\Bitrix24\Connection;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use LogicException;
use MongoDB\BSON\Binary;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Cursor;
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
    protected string $collection;

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
     * All of the available clause operators.
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
//        '&',
//        '|',
//        '^',
//        '<<',
//        '>>',
//        'rlike',
//        'regexp',
//        'not regexp',
//        'exists',
//        'type',
//        'mod',
//        'where',
//        'all',
//        'size',
//        'regex',
//        'not regex',
//        'text',
//        'slice',
//        'elemmatch',
//        'geowithin',
//        'geointersects',
//        'near',
//        'nearsphere',
//        'geometry',
//        'maxdistance',
//        'center',
//        'centersphere',
//        'box',
//        'polygon',
//        'uniquedocs',
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
     * Set the cursor hint.
     *
     * @param mixed $index
     *
     * @return $this
     */
    public function hint(mixed $index): static
    {
        $this->hint = $index;

        return $this;
    }

    /** @inheritdoc */
    public function find($id, $columns = [])
    {
        $item = $this->connection->getClient()->getScope($this->collection)->get($id);

        if (!$item) {
            return null;
        }

        if (empty($columns) || in_array('*', $columns)) {
            return $item;
        }

        return Arr::only($item, $columns);
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
        return $this->getFresh($columns);
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
     * Return the MongoDB query to be run in the form of an element array like ['method' => [arguments]].
     *
     * Example: ['find' => [['name' => 'John Doe'], ['projection' => ['birthday' => 1]]]]
     *
     * @return array<string, mixed[]>
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
//        assert(count($command) >= 1, 'At least one method call is required to execute a query');

        $result = $this->collection;
        foreach ($command as $method => $arguments) {
            $result = call_user_func_array([$result, $method], $arguments);
        }

        // countDocuments method returns int, wrap it to the format expected by the framework
        if (is_int($result)) {
            $result = [
                [
                    '_id'       => null,
                    'aggregate' => $result,
                ],
            ];
        }

        if ($returnLazy) {
            return LazyCollection::make(function () use ($result) {
                foreach ($result as $item) {
                    yield $item;
                }
            });
        }

        if ($result instanceof Cursor) {
            $result = $result->toArray();
        }

        return new Collection($result);
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
            'collection' => $this->collection,
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
    public function aggregate($function, $columns = [])
    {
        $this->aggregate = [
            'function' => $function,
            'columns' => $columns,
        ];

        $previousColumns = $this->columns;

        // We will also back up the select bindings since the select clause will be
        // removed when performing the aggregate function. Once the query is run
        // we will add the bindings back onto this query so they can get used.
        $previousSelectBindings = $this->bindings['select'];

        $this->bindings['select'] = [];

        $results = $this->get($columns);

        // Once we have executed the query, we will reset the aggregate property so
        // that more select queries can be executed against the database without
        // the aggregate value getting in the way when the grammar builds it.
        $this->aggregate          = null;
        $this->columns            = $previousColumns;
        $this->bindings['select'] = $previousSelectBindings;

        if (isset($results[0])) {
            $result = (array) $results[0];

            return $result['aggregate'];
        }
    }

    /** @inheritdoc */
    public function exists()
    {
        return $this->first() !== null;
    }

    /** @inheritdoc */
    public function distinct($column = false)
    {
        $this->distinct = true;

        if ($column) {
            $this->columns = [$column];
        }

        return $this;
    }

    /**
     * @param int|string|array $direction
     *
     * @inheritdoc
     */
    public function orderBy($column, $direction = 'asc')
    {
        if (is_string($direction)) {
            $direction = match ($direction) {
                'asc', 'ASC' => 1,
                'desc', 'DESC' => -1,
                default => throw new InvalidArgumentException('Order direction must be "asc" or "desc".'),
            };
        }

        $column = (string) $column;
        if ($column === 'natural') {
            $this->orders['$natural'] = $direction;
        } else {
            $this->orders[$column] = $direction;
        }

        return $this;
    }

    /**
     * @param list{mixed, mixed}|CarbonPeriod $values
     *
     * @inheritdoc
     */
    public function whereBetween($column, iterable $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        if ($values instanceof Collection) {
            $values = $values->all();
        }

        if (is_array($values) && (! A($values) || count($values) !== 2)) {
            throw new InvalidArgumentException('Between $values must be a list with exactly two elements: [min, max]');
        }

        $this->wheres[] = [
            'column'  => $column,
            'type'    => $type,
            'boolean' => $boolean,
            'values'  => $values,
            'not'     => $not,
        ];

        return $this;
    }

    /** @inheritdoc */
    public function insert(array $values)
    {
        // Allow empty insert batch for consistency with Eloquent SQL
        if ($values === []) {
            return true;
        }

        // Since every insert gets treated like a batch insert, we will have to detect
        // if the user is inserting a single document or an array of documents.
        $batch = true;

        foreach ($values as $value) {
            // As soon as we find a value that is not an array we assume the user is
            // inserting a single document.
            if (! is_array($value)) {
                $batch = false;
                break;
            }
        }

        if (! $batch) {
            $values = [$values];
        }

        $options = $this->inheritConnectionOptions();

        $result = $this->collection->insertMany($values, $options);

        return $result->isAcknowledged();
    }

    /** @inheritdoc */
    public function insertGetId(array $values, $sequence = null)
    {
        $options = $this->inheritConnectionOptions();

        $result = $this->collection->insertOne($values, $options);

        if (! $result->isAcknowledged()) {
            return null;
        }

        if ($sequence === null || $sequence === '_id') {
            return $result->getInsertedId();
        }

        return $values[$sequence];
    }

    /** @inheritdoc */
    public function update(array $values, array $options = [])
    {
        // Use $set as default operator for field names that are not in an operator
        foreach ($values as $key => $value) {
            if (is_string($key) && str_starts_with($key, '$')) {
                continue;
            }

            $values['$set'][$key] = $value;
            unset($values[$key]);
        }

        $options = $this->inheritConnectionOptions($options);

        return $this->performUpdate($values, $options);
    }

    /** @inheritdoc */
    public function increment($column, $amount = 1, array $extra = [], array $options = [])
    {
        $query = ['$inc' => [(string) $column => $amount]];

        if (! empty($extra)) {
            $query['$set'] = $extra;
        }

        // Protect
        $this->where(function ($query) use ($column) {
            $query->where($column, 'exists', false);

            $query->orWhereNotNull($column);
        });

        $options = $this->inheritConnectionOptions($options);

        return $this->performUpdate($query, $options);
    }

    /** @inheritdoc */
    public function decrement($column, $amount = 1, array $extra = [], array $options = [])
    {
        return $this->increment($column, -1 * $amount, $extra, $options);
    }

    /** @inheritdoc */
    public function chunkById($count, callable $callback, $column = '_id', $alias = null)
    {
        return parent::chunkById($count, $callback, $column, $alias);
    }

    /** @inheritdoc */
    public function forPageAfterId($perPage = 15, $lastId = 0, $column = '_id')
    {
        return parent::forPageAfterId($perPage, $lastId, $column);
    }

    /** @inheritdoc */
    public function pluck($column, $key = null)
    {
        $results = $this->get($key === null ? [$column] : [$column, $key]);

        // Convert ObjectID's to strings
        if (((string) $key) === '_id') {
            $results = $results->map(function ($item) {
                $item['_id'] = (string) $item['_id'];

                return $item;
            });
        }

        $p = Arr::pluck($results, $column, $key);

        return new Collection($p);
    }

    /** @inheritdoc */
    public function delete($id = null)
    {
        // If an ID is passed to the method, we will set the where clause to check
        // the ID to allow developers to simply and quickly remove a single row
        // from their database without manually specifying the where clauses.
        if ($id !== null) {
            $this->where('_id', '=', $id);
        }

        $wheres  = $this->compileWheres();
        $options = $this->inheritConnectionOptions();

        if (is_int($this->limit)) {
            if ($this->limit !== 1) {
                throw new LogicException(sprintf('Delete limit can be 1 or null (unlimited). Got %d', $this->limit));
            }

            $result = $this->collection->deleteOne($wheres, $options);
        } else {
            $result = $this->collection->deleteMany($wheres, $options);
        }

        if ($result->isAcknowledged()) {
            return $result->getDeletedCount();
        }

        return 0;
    }

    /** @inheritdoc */
    public function from($collection, $as = null)
    {
        if ($collection) {
            $this->collection = $this->connection->getCollection($collection);
        }

        return parent::from($collection);
    }

    public function truncate(): bool
    {
        $options = $this->inheritConnectionOptions();
        $result  = $this->collection->deleteMany([], $options);

        return $result->isAcknowledged();
    }

    /**
     * Get an array with the values of a given column.
     *
     * @deprecated Use pluck instead.
     *
     * @param  string $column
     * @param  string $key
     *
     * @return Collection
     */
    public function lists($column, $key = null)
    {
        return $this->pluck($column, $key);
    }

    /** @inheritdoc */
    public function raw($value = null)
    {
        // Execute the closure on the mongodb collection
        if ($value instanceof Closure) {
            return call_user_func($value, $this->collection);
        }

        // Create an expression for the given value
        if ($value !== null) {
            return new Expression($value);
        }

        // Quick access to the mongodb collection
        return $this->collection;
    }

    /**
     * Append one or more values to an array.
     *
     * @param  string|array $column
     * @param  mixed        $value
     * @param  bool         $unique
     *
     * @return int
     */
    public function push($column, $value = null, $unique = false)
    {
        // Use the addToSet operator in case we only want unique items.
        $operator = $unique ? '$addToSet' : '$push';

        // Check if we are pushing multiple values.
        $batch = is_array($value) && array_is_list($value);

        if (is_array($column)) {
            if ($value !== null) {
                throw new InvalidArgumentException(sprintf('2nd argument of %s() must be "null" when 1st argument is an array. Got "%s" instead.', __METHOD__, get_debug_type($value)));
            }

            $query = [$operator => $column];
        } elseif ($batch) {
            $query = [$operator => [(string) $column => ['$each' => $value]]];
        } else {
            $query = [$operator => [(string) $column => $value]];
        }

        return $this->performUpdate($query);
    }

    /**
     * Remove one or more values from an array.
     *
     * @param  string|array $column
     * @param  mixed        $value
     *
     * @return int
     */
    public function pull($column, $value = null)
    {
        // Check if we passed an associative array.
        $batch = is_array($value) && array_is_list($value);

        // If we are pulling multiple values, we need to use $pullAll.
        $operator = $batch ? '$pullAll' : '$pull';

        if (is_array($column)) {
            $query = [$operator => $column];
        } else {
            $query = [$operator => [$column => $value]];
        }

        return $this->performUpdate($query);
    }

    /**
     * Remove one or more fields.
     *
     * @param  string|string[] $columns
     *
     * @return int
     */
    public function drop($columns)
    {
        if (! is_array($columns)) {
            $columns = [$columns];
        }

        $fields = [];

        foreach ($columns as $column) {
            $fields[$column] = 1;
        }

        $query = ['$unset' => $fields];

        return $this->performUpdate($query);
    }

    /**
     * @return static
     *
     * @inheritdoc
     */
    public function newQuery()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Perform an update query.
     *
     * @param  array $query
     *
     * @return int
     */
    protected function performUpdate($query, array $options = [])
    {
        // Update multiple items by default.
        if (! array_key_exists('multiple', $options)) {
            $options['multiple'] = true;
        }

        $options = $this->inheritConnectionOptions($options);

        $wheres = $this->compileWheres();
        $result = $this->collection->updateMany($wheres, $query, $options);
        if ($result->isAcknowledged()) {
            return $result->getModifiedCount() ? $result->getModifiedCount() : $result->getUpsertedCount();
        }

        return 0;
    }

    /**
     * Add a basic where clause to the query.
     *
     * If 1 argument, the signature is: where(array|Closure $where)
     * If 2 arguments, the signature is: where(string $column, mixed $value)
     * If 3 arguments, the signature is: where(string $colum, string $operator, mixed $value)
     *
     * @param  Closure|string|array $column
     * @param  mixed                $operator
     * @param  mixed                $value
     * @param  string               $boolean
     *
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $params = func_get_args();

        // Remove the leading $ from operators.
        if (func_num_args() >= 3) {
            $operator = &$params[1];

            if (is_string($operator) && str_starts_with($operator, '$')) {
                $operator = substr($operator, 1);
            }
        }

        if (func_num_args() === 1 && ! is_array($column) && ! is_callable($column)) {
            throw new ArgumentCountError(sprintf('Too few arguments to function %s(%s), 1 passed and at least 2 expected when the 1st is not an array or a callable', __METHOD__, var_export($column, true)));
        }

        if (is_float($column) || is_bool($column) || $column === null) {
            throw new InvalidArgumentException(sprintf('First argument of %s must be a field path as "string". Got "%s"', __METHOD__, get_debug_type($column)));
        }

        return parent::where(...$params);
    }

    /**
     * Compile the where array.
     *
     * @return array
     */
    protected function compileWheres(): array
    {
        // The wheres to compile.
        $wheres = $this->wheres ?: [];

        // We will add all compiled wheres to this array.
        $compiled = [];

        foreach ($wheres as $i => &$where) {
            // Make sure the operator is in lowercase.
            if (isset($where['operator'])) {
                $where['operator'] = strtolower($where['operator']);

                // Convert aliased operators
                if (isset($this->conversion[$where['operator']])) {
                    $where['operator'] = $this->conversion[$where['operator']];
                }
            }

            // Convert column name to string to use as array key
            if (isset($where['column'])) {
                $where['column'] = (string) $where['column'];
            }

            // Convert DateTime values to B24 datetime format.
            if (isset($where['value'])) {
                if (is_array($where['value'])) {
                    array_walk_recursive($where['value'], function (&$item) {
                        if ($item instanceof DateTimeInterface) {
                            $item = $item->format(static::DATETIME_FORMAT);
                        }
                    });
                } else {
                    if ($where['value'] instanceof DateTimeInterface) {
                        $where['value'] = $where['value']->format(static::DATETIME_FORMAT);
                    }
                }
            } elseif (isset($where['values'])) {
                if (is_array($where['values'])) {
                    array_walk_recursive($where['values'], function (&$item, $key) {
                        if ($item instanceof DateTimeInterface) {
                            $item = $item->format(static::DATETIME_FORMAT);
                        }
                    });
                } elseif ($where['values'] instanceof CarbonPeriod) {
                    $where['values'] = [
                        $where['values']->getStartDate()->format(static::DATETIME_FORMAT),
                        $where['values']->getEndDate()->format(static::DATETIME_FORMAT),
                    ];
                }
            }

            // In a sequence of "where" clauses, the logical operator of the
            // first "where" is determined by the 2nd "where".
            // $where['boolean'] = "and", "or", "and not" or "or not"
            if (
                $i === 0 && count($wheres) > 1
                && str_starts_with($where['boolean'], 'and')
                && str_starts_with($wheres[$i + 1]['boolean'], 'or')
            ) {
                $where['boolean'] = 'or' . (str_ends_with($where['boolean'], 'not') ? ' not' : '');
            }

            // We use different methods to compile different wheres.
            $method = 'compileWhere' . $where['type'];
            $result = $this->{$method}($where);

            // Negate the expression
            if (str_ends_with($where['boolean'], 'not')) {
                $result = ['$nor' => [$result]];
            }

            // Wrap the where with an $or operator.
            if (str_starts_with($where['boolean'], 'or')) {
                $result = ['$or' => [$result]];
                // phpcs:ignore Squiz.ControlStructures.ControlSignature.SpaceAfterCloseBrace
            }

            // If there are multiple wheres, we will wrap it with $and. This is needed
            // to make nested wheres work.
            elseif (count($wheres) > 1) {
                $result = ['$and' => [$result]];
            }

            // Merge the compiled where with the others.
            // array_merge_recursive can't be used here because it converts int keys to sequential int.
            foreach ($result as $key => $value) {
                if (in_array($key, ['$and', '$or', '$nor'])) {
                    $compiled[$key] = array_merge($compiled[$key] ?? [], $value);
                } else {
                    $compiled[$key] = $value;
                }
            }
        }

        return $compiled;
    }

    /**
     * @param  array $where
     *
     * @return array
     */
    protected function compileWhereBasic(array $where): array
    {
        $where['operator'] ??= '=';
        return [$where['operator'].$where['column'] => $where['value']];
    }

    protected function compileWhereNested(array $where): mixed
    {
        return $where['query']->compileWheres();
    }

    protected function compileWhereIn(array $where): array
    {
        return $this->compileWhereBasic($where);
    }

    protected function compileWhereNotIn(array $where): array
    {
        $where['operator'] = '!=';

        return $this->compileWhereBasic($where);
    }

    protected function compileWhereNull(array $where): array
    {
        $where['operator'] = '=';
        $where['value']    = null;

        return $this->compileWhereBasic($where);
    }

    protected function compileWhereNotNull(array $where): array
    {
        $where['operator'] = '!=';
        $where['value']    = null;

        return $this->compileWhereBasic($where);
    }

    protected function compileWhereBetween(array $where): array
    {
        $column = $where['column'];
        $not    = $where['not']; //is not supported in current version
        $values = $where['values'];

        if ($not) {
            return [];
        }

        return [
            $column => [
                '>=' => $values[0],
                '<=' => $values[1],
            ],
        ];
    }

    protected function compileWhereDate(array $where): array
    {
        $startOfDay = Carbon::parse($where['value'])->startOfDay()->format(static::DATETIME_FORMAT);
        $endOfDay   = Carbon::parse($where['value'])->endOfDay()->format(static::DATETIME_FORMAT);

        return match ($where['operator']) {
            '', '=' => [
                '>='.$where['column'] => $startOfDay,
                '<='.$where['column'] => $endOfDay,
            ],
            '<', '>=' => [
                $where['operator'].$where['column'] => $startOfDay,
            ],
            'gt', 'lte' => [
                $where['operator'].$where['column'] => $endOfDay,
            ],
            default => []
        };
    }

    protected function compileWhereMonth(array $where): array
    {
        return [];
    }

    protected function compileWhereDay(array $where): array
    {
        return [];
    }

    protected function compileWhereYear(array $where): array
    {
        return [];
    }

    protected function compileWhereTime(array $where): array
    {
        return [];
    }

    protected function compileWhereRaw(array $where): array
    {
        return [];
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
    public function whereFullText($columns, $value, array $options = [], $boolean = 'and')
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
