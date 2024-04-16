<?php

namespace Helpers;

use InvalidArgumentException;
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
     * Retrieves limit commands generator instance
     *
     * @return ListCommandsGenerator
     */
    private function getGenerator(): ListCommandsGenerator
    {
        return new ListCommandsGenerator();
    }
}