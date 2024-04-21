<?php

namespace Pranju\Bitrix24\Tests\Unit\Query;

use Carbon\Carbon;
use Closure;
use DateTime;
use Generator;
use Illuminate\Support\Facades\DB;
use Pranju\Bitrix24\Core\Cmd;
use Pranju\Bitrix24\Query\Builder;
use Pranju\Bitrix24\Query\Grammar;
use Pranju\Bitrix24\Tests\TestCase;
use Workbench\App\Console\TestCommand;

class GrammarTest extends TestCase
{
    public static array $operators = [
        '' => '',
        '=' => '=',
        '<' => '<',
        '>' => '>',
        '<=' => '<=',
        '>=' => '>=',
        '<>' => '!=',
        '!=' => '!=',
        '!' => '!',
        'like' => '',
        'ilike' => '',
    ];

    public static array $notOperators = Grammar::INVERSE_OPERATORS;

    public function testCompileInsertGetId(): void
    {

    }

    public function testCompileUpdate(): void
    {

    }

    public function testCompileDelete(): void
    {

    }

    public function testCompileExists(): void
    {

    }

    public function testCompileInsert(): void
    {

    }

    public function testCompileInsertOrIgnore(): void
    {

    }

    public function testCompileSelect(): void
    {

    }

    public function testCompileWheresToArray(): void
    {

    }

    protected function testWhereBasic(): void
    {

    }

    protected function testWhereNull(): void
    {

    }

    protected function getWhereNullData(): array
    {
        return [];
    }

    protected function testWhereNotNull(): void
    {

    }

    protected function testWhereIn(): void
    {

    }

    protected function testWhereNotIn(): void
    {

    }

    protected function testWhereBetween(): void
    {

    }

    public function testWhereFullText(): void
    {

    }

    protected function testWhereYear(): void
    {

    }

    /**
     * @param Closure $callback
     * @param array $expected
     * @return void
     * @see          Grammar::compileWheres()
     * @dataProvider getWheresData
     */
    public function testCompileWheres(Closure $callback, array $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->getGrammar()->compileWheres(
                $callback($this->query())
            ),
        );
    }

    /**
     * @return Generator
     */
    public static function getWheresData(): Generator
    {
        foreach (static::$operators as $before => $after) {
            yield "basic $before" => [
                fn(Builder $query) => $query->where('col', $before, 1),
                [$after.'col' => 1],
            ];
        }

        foreach (static::$notOperators as $before => $after) {
            yield "basic not $before" => [
                fn(Builder $query) => $query->whereNot('col', $before, 1),
                [$after.'col' => 1],
            ];
        }

        yield 'basic nothing' => [
            fn(Builder $query) => $query->where('col', 1),
            ['=col' => 1],
        ];

        yield 'null' => [
            fn(Builder $query) => $query->whereNull('col'),
            ['=col' => null],
        ];

        yield 'not null' => [
            fn(Builder $query) => $query->whereNotNull('col'),
            ['!=col' => null],
        ];

        yield 'date nothing' => [
            fn(Builder $query) => $query->whereDate('col', '2023-03-05'),
            ['>=col' => '2023-03-05', '<=col' => '2023-03-05 23:59:59'],
        ];

        yield 'date nothing time' => [
            fn(Builder $query) => $query->whereDate('col', '2023-03-05 15:03:56'),
            ['>=col' => '2023-03-05', '<=col' => '2023-03-05 23:59:59'],
        ];

        yield 'date nothing datetime' => [
            fn(Builder $query) => $query->whereDate('col', new DateTime('2023-03-05 15:03:56')),
            ['>=col' => '2023-03-05', '<=col' => '2023-03-05 23:59:59'],
        ];

        yield 'date nothing carbon' => [
            fn(Builder $query) => $query->whereDate('col', new Carbon('2023-03-05 15:03:56')),
            ['>=col' => '2023-03-05', '<=col' => '2023-03-05 23:59:59'],
        ];

        yield 'date =' => [
            fn(Builder $query) => $query->whereDate('col', '=', '2023-03-05'),
            ['>=col' => '2023-03-05', '<=col' => '2023-03-05 23:59:59'],
        ];

        yield 'date >' => [
            fn(Builder $query) => $query->whereDate('col', '>', '2023-03-05'),
            ['>col' => '2023-03-05 23:59:59'],
        ];

        yield 'date <' => [
            fn(Builder $query) => $query->whereDate('col', '<', '2023-03-05'),
            ['<col' => '2023-03-05'],
        ];

        yield 'date >=' => [
            fn(Builder $query) => $query->whereDate('col', '>=', '2023-03-05'),
            ['>=col' => '2023-03-05'],
        ];

        yield 'date <=' => [
            fn(Builder $query) => $query->whereDate('col', '<=', '2023-03-05'),
            ['<=col' => '2023-03-05 23:59:59'],
        ];

        yield 'in array' => [
            fn(Builder $query) => $query->whereIn('col', [1, 7, 'cat']),
            ['=col' => [1, 7, 'cat']],
        ];

        yield 'in collection' => [
            fn(Builder $query) => $query->whereIn('col', collect([1, 7, 'cat'])),
            ['=col' => [1, 7, 'cat']],
        ];

        yield 'not in array' => [
            fn(Builder $query) => $query->whereNotIn('col', [1, 7, 'cat']),
            ['!=col' => [1, 7, 'cat']],
        ];

        yield 'not in collection' => [
            fn(Builder $query) => $query->whereNotIn('col', collect([1, 7, 'cat'])),
            ['!=col' => [1, 7, 'cat']],
        ];

        yield 'between array' => [
            fn(Builder $query) => $query->whereBetween('col', ['2023-03-05', '2024-02-17']),
            ['>=col' => '2023-03-05', '<=col' => '2024-02-17'],
        ];

        yield 'between collection' => [
            fn(Builder $query) => $query->whereBetween('col', collect(['2023-03-05', '2024-02-17'])),
            ['>=col' => '2023-03-05', '<=col' => '2024-02-17'],
        ];

        yield 'between dates' => [
            fn(Builder $query) => $query->whereBetween('col', [
                new DateTime('2023-03-05'),
                new DateTime('2024-02-17'),
            ]),
            ['>=col' => '2023-03-05', '<=col' => '2024-02-17'],
        ];

        yield 'between datetimes' => [
            fn(Builder $query) => $query->whereBetween('col', [
                new DateTime('2023-03-05 17:23'),
                new DateTime('2024-02-17 18:54:34'),
            ]),
            ['>=col' => '2023-03-05 17:23:00', '<=col' => '2024-02-17 18:54:34'],
        ];

        yield 'between carbon' => [
            fn(Builder $query) => $query->whereBetween('col', [
                Carbon::make('2023-03-05 17:23'),
                Carbon::make('2024-02-17 18:54:34'),
            ]),
            ['>=col' => '2023-03-05 17:23:00', '<=col' => '2024-02-17 18:54:34'],
        ];

        yield 'year nothing' => [
            fn(Builder $query) => $query->whereYear('col', '2023'),
            ['>=col' => '2023-01-01', '<=col' => '2023-12-31 23:59:59'],
        ];

        yield 'year =' => [
            fn(Builder $query) => $query->whereYear('col', '=', '2023'),
            ['>=col' => '2023-01-01', '<=col' => '2023-12-31 23:59:59'],
        ];

        yield 'year >' => [
            fn(Builder $query) => $query->whereYear('col', '>', '2023'),
            ['>col' => '2023-12-31 23:59:59'],
        ];

        yield 'year <' => [
            fn(Builder $query) => $query->whereYear('col', '<', '2023'),
            ['<col' => '2023-01-01'],
        ];

        yield 'year >=' => [
            fn(Builder $query) => $query->whereYear('col', '>=', '2023'),
            ['>=col' => '2023-01-01'],
        ];

        yield 'year <=' => [
            fn(Builder $query) => $query->whereYear('col', '<=', '2023'),
            ['<=col' => '2023-12-31 23:59:59'],
        ];

        yield 'fulltext <=' => [
            fn(Builder $query) => $query->whereFullText('col', 'cat'),
            ['col' => 'cat'],
        ];
    }

    public function testCompileFind(): void
    {
        app(TestCommand::class)->handle();
    }

    /**
     * @param array $orders
     * @param array $expected
     * @return void
     * @see Grammar::compileOrders()
     * @dataProvider getOrdersData
     */
    public function _testCompileOrders(array $orders, array $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->getGrammar()->compileOrders($this->query(), $orders)
        );
    }

    /**
     * @return \array[][]
     */
    public static function getOrdersData(): array
    {
        return [
            [[], []],
            [['ID' => 'ASC'], ['ID' => 'ASC']],
            [
                ['ID' => 'ASC', 'NAME' => 'DESC'],
                ['ID' => 'ASC', 'NAME' => 'DESC'],
            ],
        ];
    }

    protected function testCompileColumns(): void
    {

    }

    /**
     * @param int $offset
     * @param int $expected
     * @return void
     * @see Grammar::compileOffset()
     * @dataProvider getOffsetData
     */
    public function _testCompileOffset(int $offset, int $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->getGrammar()->compileOffset($this->query(), $offset)
        );
    }

    /**
     * @return array[]
     */
    public static function getOffsetData(): array
    {
        return [
            [0, 0],
            [3, 0],
            [50, 50],
            [54, 50],
        ];
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param int $expected
     * @return void
     * @see Grammar::compileLimit()
     * @dataProvider getLimitData
     */
    public function _testCompileLimit(int $offset, int $limit, int $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->getGrammar()->compileLimit($this->query()->offset($offset), $limit)
        );
    }

    /**
     * @return array[]
     */
    public static function getLimitData(): array
    {
        return [
            [0, 50, 50],
            [0, 49, 50],
            [2, 50, 100],
            [57, 43, 50],
            [157, 143, 150],
        ];
    }

    /**
     * @param mixed $value
     * @param mixed $expected
     * @return void
     * @see Grammar::parameter()
     * @dataProvider getValuesData
     */
    public function testParameter(mixed $value, mixed $expected): void
    {
        $this->assertEquals($expected, $this->getGrammar()->parameter($value));
    }

    /**
     * @param mixed $value
     * @param mixed $expected
     * @return void
     * @see Grammar::getValue()
     * @dataProvider getValuesData
     */
    public function testGetValue(mixed $value, mixed $expected): void
    {
        $this->assertEquals($expected, $this->getGrammar()->getValue($value));
    }

    public static function getValuesData(): array
    {
        return [
            'date_from_carbon' => [Carbon::make('2023-01-07'), '2023-01-07'],
            'date_from_datetime' => [new DateTime('2023-01-07'), '2023-01-07'],
            'datetime_from_carbon' => [Carbon::make('2023-01-07 20:07:13'), '2023-01-07 20:07:13'],
            'datetime_from_datetime' => [new DateTime('2023-01-07 20:07:13'), '2023-01-07 20:07:13'],
            'arrayable' => [collect([1, 2, 4]), [1, 2, 4]],
            'stringable' => [Cmd::make('test', ['t' => 1]), 'test?t=1'],
        ];
    }

    /**
     * Retrieves new query builder instance
     *
     * @return Builder
     */
    private function query(): Builder
    {
        return DB::query();
    }

    /**
     * Retrieves new Grammar
     *
     * @return Grammar
     */
    private function getGrammar(): Grammar
    {
        return new Grammar();
    }
}