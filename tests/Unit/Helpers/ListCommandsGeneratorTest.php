<?php

namespace Helpers;

use InvalidArgumentException;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Core\Cmd;
use Pranju\Bitrix24\Helpers\ListCommandsGenerator;
use Pranju\Bitrix24\Tests\TestCase;

class ListCommandsGeneratorTest extends TestCase
{
    /**
     * @param int $limit
     * @param int $startId
     * @param string $pattern
     * @param array $expected
     * @return void
     * @see ListCommandsGenerator::generateFilters()
     * @dataProvider getGenerateFiltersData
     */
    public function testGenerateFilters(
        int $limit,
        int $startId,
        string $pattern,
        array $expected
    ): void
    {
        $this->assertEquals(
            $expected,
            (new ListCommandsGenerator())->generateFilters($limit, $startId, $pattern),
        );
    }

    /**
     * @return void
     * @see ListCommandsGenerator::generateFilters()
     */
    public function testGenerateExceptions(): void
    {
        $this->assertThrows(
            fn() => $this->getGenerator()->generateFilters(0),
            InvalidArgumentException::class,
            'Limit must be greater than 0',
        );
        
        $this->assertThrows(
            fn() => $this->getGenerator()->generateFilters(-59),
            InvalidArgumentException::class,
            'Limit must be greater than 0',
        );

        $this->assertThrows(
            fn() => $this->getGenerator()->generateFilters(50, 0, '[ID]'),
            InvalidArgumentException::class,
            'Pattern must contain "{index}" substring',
        );
    }

    /**
     * @param Command[] $expectedCommands
     * @param mixed ...$arguments
     * @return void
     * @see          ListCommandsGenerator::generateBatch()
     * @dataProvider getGenerateBatchData
     */
    public function testGenerateBatch(array $expectedCommands, ...$arguments): void
    {
        $this->assertEquals(
            array_map(
                fn ($command) => $command->getData(),
                $expectedCommands
            ),
            array_map(
                fn ($command) => $command->getData(),
                $this->getGenerator()->generateBatch(...$arguments)->all()
            ),
        );
    }

    /**
     * @return array
     */
    public static function getGenerateFiltersData(): array
    {
        return [
            'single_zero_id' => [
                1,
                0,
                '[{index}]',
                ['q0' => 0],
            ],
            'single_not_zero_id' => [
                1,
                37,
                '[{index}][ID]',
                ['q0' => 37],
            ],
            'limit_50' => [
                50,
                0,
                '[{index}][ID]',
                ['q0' => 0],
            ],
            'limit_51' => [
                51,
                0,
                '[{index}][ID]',
                [
                    'q0' => 0,
                    'q1' => '$result[q0][49][ID]',
                ],
            ],
            'limit_100' => [
                100,
                0,
                '[{index}][ID]',
                [
                    'q0' => 0,
                    'q1' => '$result[q0][49][ID]',
                ],
            ],
            'limit_137' => [
                137,
                0,
                '[{index}][ID]',
                [
                    'q0' => 0,
                    'q1' => '$result[q0][49][ID]',
                    'q2' => '$result[q1][49][ID]',
                ],
            ],
            'changed_pattern' => [
                100,
                0,
                '[{index}][items][ID]',
                [
                    'q0' => 0,
                    'q1' => '$result[q0][49][items][ID]'
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getGenerateBatchData(): array
    {
        return [
             'one command without start id argument' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => [],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list'),
                 1,
             ],
             'one command with start id argument' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => ['>ID' => 4],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list'),
                 1,
                 4,
             ],
             'one command with start id' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => ['>ID' => 5],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list', [
                     'filter' => ['>ID' => 5]
                 ]),
                 1,
             ],
             'start id in command greater than in argument' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => ['>ID' => 6],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list', [
                     'filter' => ['>ID' => 5]
                 ]),
                 1,
                 6,
             ],
             'start id in command lower than in argument' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => ['>ID' => 5],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list', [
                     'filter' => ['>ID' => 5]
                 ]),
                 1,
                 3,
             ],
             'two commands' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => [],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ]),
                     'q1' => Cmd::make('crm.lead.list', [
                         'filter' => ['>ID' => '$result[q0][49][ID]'],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list', []),
                 51,
             ],
             'one command start 50' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => [],
                         'order' => ['ID' => 'ASC'],
                         'start' => 50,
                         'limit' => 50,
                     ]),
                     'q1' => Cmd::make('crm.lead.list', [
                         'filter' => ['>ID' => '$result[q0][49][ID]'],
                         'order' => ['ID' => 'ASC'],
                         'start' => -1,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list', [
                     'start' => 50
                 ]),
                 51,
             ],
             'two commands start 50' => [
                 [
                     'q0' => Cmd::make('crm.lead.list', [
                         'filter' => [],
                         'order' => ['ID' => 'ASC'],
                         'start' => 50,
                         'limit' => 50,
                     ])
                 ],
                 Cmd::make('crm.lead.list', [
                     'start' => 50
                 ]),
                 1,
             ]
        ];
    }

    /**
     * Retrieves limit commands generator instance
     *
     * @return ListCommandsGenerator
     */
    private function getGenerator(): ListCommandsGenerator
    {
        return new ListCommandsGenerator();
    }
}