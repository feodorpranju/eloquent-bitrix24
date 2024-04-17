<?php


namespace Pranju\Bitrix24\Tests\Unit\Query;


use Carbon\Carbon;
use Closure;
use Pranju\Bitrix24\Connection;
use Pranju\Bitrix24\Core\Client;
use Pranju\Bitrix24\Query\Builder;
use Pranju\Bitrix24\Query\Grammar;
use Pranju\Bitrix24\Query\Processor;
use Pranju\Bitrix24\Tests\TestCase;
use Mockery;

class BuilderTest extends TestCase
{
    /**
     * @param array $expected
     * @param Closure $build
     * @dataProvider builderToB24DataProvider
     */
    public function testToB24(array $expected, Closure $build): void
    {
        $builder = $build(self::getBuilder());
        $this->assertInstanceOf(Builder::class, $builder);
        $b24 = $builder->toB24();

        $this->assertEquals($expected, $b24, var_export($b24, true));
    }

    public static function builderToB24DataProvider(): iterable
    {
        /**
         * Builder::aggregate() and Builder::count() cannot be tested because they return the result,
         * without modifying the builder.
         */
        $date = Carbon::make('2016-07-12 15:30:00');

        yield 'select replaces previous select' => [
            ['select' => ['bar']],
            fn (Builder $builder) => $builder->select('foo')->select('bar'),
        ];

        yield 'select array' => [
            ['select' => ['foo', 'bar']],
            fn (Builder $builder) => $builder->select(['foo', 'bar']),
        ];

        /** @see DatabaseQueryBuilderTest::testAddingSelects */
        yield 'addSelect' => [
            ['select' => ['foo', 'bar', 'baz', 'bom']],
            fn (Builder $builder) => $builder->select('foo')
                ->addSelect('bar')
                ->addSelect(['baz', 'boom'])
                ->addSelect('bar'),
        ];

        yield 'select all' => [
            [['select' => ['*']]],
            fn (Builder $builder) => $builder->select('*'),
        ];

        yield 'find all with select' => [
            ['select' => ['foo', 'bar']],
            fn (Builder $builder) => $builder->select('foo', 'bar'),
        ];

        yield 'find equals' => [
            ['filter' => ['=foo' => 'bar']],
            fn (Builder $builder) => $builder->where('foo', 'bar'),
        ];

        yield 'find with numeric field name' => [
            ['filter' => ['=123' => 'bar']],
            fn (Builder $builder) => $builder->where(123, 'bar'),
        ];

        yield 'where with single array of conditions' => [
            ['filter' => ['=foo' => 1,  '=bar' => 1]],
            fn (Builder $builder) => $builder->where(['foo' => 1, 'bar' => 2]),
        ];

        yield 'find > date' => [
            ['filter' => ['>foo' => $date->format(Builder::DATETIME_FORMAT)]],
            fn (Builder $builder) => $builder->where('foo', '>', $date),
        ];

        /** @see DatabaseQueryBuilderTest::testBasicWhereIns */
        yield 'whereIn' => [
            ['filter' => ['=foo' => ['bar', 'baz']]],
            fn (Builder $builder) => $builder->whereIn('foo', ['bar', 'baz']),
        ];

        // Nested array are not flattened like in the Eloquent builder. B24 merges values
        $array = [['issue' => 45582], ['id' => 2], [3]];
        yield 'whereIn nested array' => [
            ['filter' => ['=id' => [45582, 2, 3]]],
            fn (Builder $builder) => $builder->whereIn('id', $array),
        ];

        //yield 'orWhereIn' => [];

        /** @see DatabaseQueryBuilderTest::testBasicWhereNotIns */
        yield 'whereNotIn' => [
            ['filter' => ['!=id' => [1, 2, 3]]],
            fn (Builder $builder) => $builder->whereNotIn('id', [1, 2, 3]),
        ];

        //yield 'orWhereNotIn' => [];

        /** @see DatabaseQueryBuilderTest::testEmptyWhereIns */
        yield 'whereIn empty array' => [
            ['filter' => ['=id' => null]],
            fn (Builder $builder) => $builder->whereIn('id', []),
        ];

        yield 'find limit offset select' => [
            ['limit' => 10, 'start' => 5, 'select' => ['foo', 'bar']],
            fn (Builder $builder) => $builder->limit(10)->offset(5)->select('foo', 'bar'),
        ];

        /** @see DatabaseQueryBuilderTest::testBasicWhereNot() */
        yield 'whereNot (multiple)' => [
            ['filter' => ['!=name' => 'foo', '=name' => 'bar']],
            fn (Builder $builder) => $builder
                ->whereNot('name', 'foo')
                ->whereNot('name', '<>', 'bar'),
        ];

        /** @see DatabaseQueryBuilderTest::testBasicOrWheres() */
        //yield 'where orWhere' => [];

        /** @see DatabaseQueryBuilderTest::testBasicOrWhereNot() */
        //yield 'orWhereNot' => [];

        //yield 'whereNot orWhere' => [];

        /** @see DatabaseQueryBuilderTest::testWhereNot() */
        yield 'whereNot callable' => [
            ['filter' => ['!=name' => 'foo']],
            fn (Builder $builder) => $builder
                ->whereNot(fn (Builder $q) => $q->where('name', 'foo')),
        ];

        yield 'where whereNot' => [
            ['filter' => ['=name' => 'bar', '!=email' => 'foo']],
            fn (Builder $builder) => $builder
                ->where('name', '=', 'bar')
                ->whereNot(function (Builder $q) {
                    $q->where('email', '=', 'foo');
                }),
        ];

        yield 'whereNot (nested)' => [
            ['filter' => ['!=name' => 'foo', '=email' => 'bar']],
            fn (Builder $builder) => $builder
                ->whereNot(function (Builder $q) {
                    $q->where('name', '=', 'foo')
                        ->whereNot('email', '<>', 'bar');
                }),
        ];

        //yield 'orWhere orWhereNot' => [];

        //yield 'where orWhereNot' => [];

        /** @see DatabaseQueryBuilderTest::testWhereNotWithArrayConditions() */
        yield 'whereNot with arrays of single condition' => [
            ['filter' => ['!=foo' => 1, '!=bar' => 2]],
            fn (Builder $builder) => $builder
                ->whereNot([['foo', 1], ['bar', 2]]),
        ];

        yield 'whereNot with single array of conditions' => [
            ['filter' => ['!=foo' => 1, '!=bar' => 2]],
            fn (Builder $builder) => $builder
                ->whereNot(['foo' => 1, 'bar' => 2]),
        ];

        yield 'whereNot with arrays of single condition with operator' => [
            ['filter' => ['!=foo' => 1, '>bar' => 2]],
            fn (Builder $builder) => $builder
                ->whereNot([
                    ['foo', 1],
                    ['bar', '<', 2],
                ]),
        ];

        //yield 'where all' => [];

        //yield 'where all nested operators' => [];

        /** @see DatabaseQueryBuilderTest::testForPage() */
        yield 'forPage' => [
            ['limit' => 20, 'start' => 40],
            fn (Builder $builder) => $builder->forPage(3, 20),
        ];

        /** @see DatabaseQueryBuilderTest::testLimitsAndOffsets() */
        yield 'offset limit' => [
            ['start' => 5, 'limit' => 10],
            fn (Builder $builder) => $builder->offset(5)->limit(10),
        ];

        yield 'offset limit zero (unset)' => [
            ['start' => 0, 'limit' => 0],
            fn (Builder $builder) => $builder
                ->offset(0)->limit(0),
        ];

        yield 'offset limit zero (reset)' => [
            ['start' => 0, 'limit' => 0],
            fn (Builder $builder) => $builder
                ->offset(5)->limit(10)
                ->offset(0)->limit(0),
        ];

        yield 'offset limit negative (unset)' => [
            ['start' => 0, 'limit' => 0],
            fn (Builder $builder) => $builder
                ->offset(-5)->limit(-10),
        ];

        yield 'offset limit null (reset)' => [
            ['start' => 0, 'limit' => 0],
            fn (Builder $builder) => $builder
                ->offset(5)->limit(10)
                ->offset(null)->limit(null),
        ];

        yield 'skip take (aliases)' => [
            ['start' => 5, 'limit' => 10],
            fn (Builder $builder) => $builder->skip(5)->limit(10),
        ];

        /** @see DatabaseQueryBuilderTest::testOrderBys() */
        yield 'orderBy multiple columns' => [
            ['order' => ['email' => 1, 'age' => -1]],
            fn (Builder $builder) => $builder
                ->orderBy('email')
                ->orderBy('age', 'desc'),
        ];

        yield 'orders = null' => [
            ['order' => null],
            function (Builder $builder) {
                $builder->orders = null;

                return $builder;
            },
        ];

        yield 'orders = []' => [
            ['order' => null],
            function (Builder $builder) {
                $builder->orders = [];

                return $builder;
            },
        ];

        yield 'multiple orders with direction' => [
            ['order' => ['email' => -1, 'age' => 1]],
            fn (Builder $builder) => $builder
                ->orderBy('email', -1)
                ->orderBy('age', 1),
        ];

        yield 'orderByDesc' => [
            ['order' => ['email' => -1]],
            fn (Builder $builder) => $builder->orderByDesc('email'),
        ];

        /** @see DatabaseQueryBuilderTest::testReorder() */
        yield 'reorder reset' => [
            ['order' => null],
            fn (Builder $builder) => $builder->orderBy('name')->reorder(),
        ];

        yield 'reorder column' => [
            ['order' => ['name' => -1]],
            fn (Builder $builder) => $builder->orderBy('name')->reorder('name', 'desc'),
        ];

        /** @see DatabaseQueryBuilderTest::testWhereBetweens() */
        yield 'whereBetween array of numbers' => [
            ['filter' => ['>=id' => 1, '<=id' => 2]],
            fn (Builder $builder) => $builder->whereBetween('id', [1, 2]),
        ];

        yield 'whereBetween nested array of numbers' => [
            ['filter' => ['>=id' => 1, '<=id' => 3]],
            fn (Builder $builder) => $builder->whereBetween('id', [[1], [2, 3]]),
        ];

        $period = now()->toPeriod(now()->addMonth());
        yield 'whereBetween CarbonPeriod' => [
            [
                'filter' => [
                    '>=created_at' => $period->start->format(Builder::DATETIME_FORMAT),
                    '<=created_at' => $period->end->format(Builder::DATETIME_FORMAT)
                ],
            ],
            fn (Builder $builder) => $builder->whereBetween('created_at', $period),
        ];

        yield 'whereBetween collection' => [
            ['filter' => ['>=id' => 1, '<=id' => 2]],
            fn (Builder $builder) => $builder->whereBetween('id', collect([1, 2])),
        ];

        /** @see DatabaseQueryBuilderTest::testOrWhereBetween() */
        //yield 'orWhereBetween array of numbers' => [];

        //yield 'orWhereBetween nested array of numbers' => [];

        //yield 'orWhereBetween collection' => [];

        //yield 'whereNotBetween array of numbers' => [];

        /** @see DatabaseQueryBuilderTest::testOrWhereNotBetween() */
        //yield 'orWhereNotBetween array of numbers' => [];

        //yield 'orWhereNotBetween nested array of numbers' => [];

        //yield 'orWhereNotBetween collection' => [];

        yield 'where like' => [
            ['filter' => ['name' => 'acme']],
            fn (Builder $builder) => $builder->where('name', 'like', 'acme'),
        ];

        yield 'where ilike' => [
            ['filter' => ['name' => 'acme']],
            fn (Builder $builder) => $builder->where('name', 'ilike', 'acme'),
        ];

        yield 'where like %' => [
            ['filter' => ['name' => ['ac', 'me']]],
            fn (Builder $builder) => $builder->where('name', 'like', '%ac%%me%'),
        ];

        yield 'where date' => [
            [
                'filter' => [
                    '>=created_at' => Carbon::make('2018-09-30')->startOfDay()->format(Builder::DATETIME_FORMAT),
                    '<=created_at' => Carbon::make('2018-09-30')->endOfDay()->format(Builder::DATETIME_FORMAT)
                ],
            ],
            fn (Builder $builder) => $builder->whereDate('created_at', '2018-09-30'),
        ];

        yield 'where date Carbon' => [
            [
                'filter' => [
                    '>=created_at' => Carbon::make('2018-09-30')->startOfDay()->format(Builder::DATETIME_FORMAT),
                    '<=created_at' => Carbon::make('2018-09-30')->endOfDay()->format(Builder::DATETIME_FORMAT)
                ],
            ],
            fn (Builder $builder) => $builder->whereDate('created_at', '=', new Carbon('2018-09-30 15:00:00 +02:00')),
        ];

        //yield 'where date !=' => [];

        yield 'where date <' => [
            [
                'filter' => [
                    '<created_at' => Carbon::make('2018-09-30')->startOfDay()->format(Builder::DATETIME_FORMAT)
                ]
            ],
            fn (Builder $builder) => $builder->whereDate('created_at', '<', '2018-09-30'),
        ];

        yield 'where date >=' => [
            [
                'filter' => [
                    '>=created_at' => Carbon::make('2018-09-30')->startOfDay()->format(Builder::DATETIME_FORMAT)
                ]
            ],
            fn (Builder $builder) => $builder->whereDate('created_at', '>=', '2018-09-30'),
        ];

        yield 'where date >' => [
            [
                'filter' => [
                    '<created_at' => Carbon::make('2018-09-30')->endOfDay()->format(Builder::DATETIME_FORMAT)
                ]
            ],
            fn (Builder $builder) => $builder->whereDate('created_at', '>', '2018-09-30'),
        ];

        yield 'where date <=' => [
            [
                'filter' => [
                    '<=created_at' => Carbon::make('2018-09-30')->endOfDay()->format(Builder::DATETIME_FORMAT)
                ]
            ],
            fn (Builder $builder) => $builder->whereDate('created_at', '<=', '2018-09-30'),
        ];

        //yield 'where day' => [];

        //yield 'where day > string' => [];

        //yield 'where month' => [];

        //yield 'where month > string' => [];

        yield 'where year' => [
            [
                'filter' => [
                    '>=created_at' => Carbon::make('2023-01-01')->startOfDay()->format(Builder::DATETIME_FORMAT),
                    '<created_at' => Carbon::make('2024-01-01')->startOfDay()->format(Builder::DATETIME_FORMAT)
                ]
            ],
            fn (Builder $builder) => $builder->whereYear('created_at', 2023),
        ];

        yield 'where year > string' => [
            [
                'filter' => [
                    '>created_at' => Carbon::make('2023-12-31')->endOfDay()->format(Builder::DATETIME_FORMAT)
                ]
            ],
            fn (Builder $builder) => $builder->whereYear('created_at', '>', '2023'),
        ];

        //yield 'where time HH:MM:SS' => [];

        //yield 'where time HH:MM' => [];

        //yield 'where time HH' => [];

        //yield 'where time DateTime' => [];

        //yield 'where time >' => [];

        /** @see DatabaseQueryBuilderTest::testLatest() */
        yield 'latest' => [
            ['order' => ['ID' => -1]],
            fn (Builder $builder) => $builder->latest(),
        ];

        yield 'latest limit' => [
            ['order' => ['ID' => -1], 'limit' => 1],
            fn (Builder $builder) => $builder->latest()->limit(1),
        ];

        yield 'latest custom field' => [
            ['order' => ['updated_at' => -1]],
            fn (Builder $builder) => $builder->latest('updated_at'),
        ];

        /** @see DatabaseQueryBuilderTest::testOldest() */
        yield 'oldest' => [
            ['order' => ['ID' => 1]],
            fn (Builder $builder) => $builder->oldest(),
        ];

        yield 'oldest limit' => [
            ['order' => ['ID' => 1], 'limit' => 1],
            fn (Builder $builder) => $builder->oldest()->limit(1),
        ];

        yield 'oldest custom field' => [
            ['order' => ['updated_at' => 1]],
            fn (Builder $builder) => $builder->oldest('updated_at'),
        ];

        //yield 'groupBy' => [];
    }

    private static function getBuilder(): Builder
    {
        $connection = Mockery::mock(Connection::class);
        $processor  = Mockery::mock(Processor::class);
        $connection->shouldReceive('getClient')->andReturn(Client::make(''));
        $connection->shouldReceive('getQueryGrammar')->andReturn(new Grammar());

        return new Builder($connection, null, $processor);
    }
}