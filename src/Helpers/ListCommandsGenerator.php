<?php

namespace Pranju\Bitrix24\Helpers;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Pranju\Bitrix24\Contracts\Batch as BatchInterface;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Core\Batch;
use Pranju\Bitrix24\Core\Cmd;

class ListCommandsGenerator
{
    /**
     * Generates Batch command with list commands
     *
     * @param Command $command
     * @param int $limit
     * @param int $startId
     * @param string $primaryKey
     * @param string $pattern Pattern between '$result[key]' and primary key
     * @return BatchInterface
     */
    public function generateBatch(
        Command $command,
        int $limit,
        int $startId = 0,
        string $primaryKey = 'ID',
        string $pattern = '[{index}]'
    ): BatchInterface
    {
        $data = $command->getData();
        $data['start'] = -1;
        $data['limit'] = 50;
        $data['order'] = array_replace(['ID' => 'ASC'], $data['order'] ?? []);
        $condition = ">$primaryKey";

        return Batch::make(
            array_map(
                fn ($filter) => Cmd::make(
                    $command->getMethod(),
                    array_replace_recursive(
                        $data,
                        ['filter' => [$condition => $filter]],
                    ),
                ),
                $this->generateFilters(
                    $limit,
                    max($data['filter'][$condition] ?? 0, $startId),
                    $pattern."[$primaryKey]"
                )
            ),
            $command->getClient(),
        );
    }

    /**
     * Generates array of filter
     *
     * @param int $limit
     * @param int $startId
     * @param string $pattern Pattern after '$result[key]'. {index} is interpolated with index
     * @return array
     */
    public function generateFilters(
        int $limit,
        int $startId = 0,
        string $pattern = '[{index}][ID]'
    ): array {
        if ($limit < 1) {
            throw new InvalidArgumentException('Limit must be greater than 0');
        }

        if (!Str::contains($pattern, '{index}')) {
            throw new InvalidArgumentException('Pattern must contain "{index}" substring');
        }

        $limit -= 50;
        $filters = ["q0" => $startId];
        $pattern = Str::replace('{index}', 49, $pattern);

        for ($i = 0; $limit > 0; $i++) {
            $limit -= 50;

            $filters['q'.($i+1)] = '$result'."[q$i]".$pattern;
        }

        return $filters;
    }
}