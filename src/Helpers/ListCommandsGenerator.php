<?php

namespace Pranju\Bitrix24\Helpers;

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
     * @param int $total
     * @param int $limit
     * @param int $startId
     * @param int $primaryKey
     * @param string $pattern Pattern between result and primary key
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
        $data['order'] = array_replace(['ID' => 'ASC'], $data['order'] ?? []);
        $condition = ">$primaryKey";
        $filters = $this->generateFilters($limit, $startId, $primaryKey, $pattern);

        return Batch::make(
            array_map(
                fn ($filter) => Cmd::make(
                    $command->getMethod(),
                    array_replace_recursive(
                        $data,
                        ['filter' => [$condition => $filter]],
                    ),
                ),
                $filters
            ),
            $command->getClient(),
        );
    }

    /**
     * Generates array of filter
     *
     * @param int $limit
     * @param int $startId
     * @param string $primaryKey
     * @param string $pattern
     * @return array
     */
    public function generateFilters(
        int $limit,
        int $startId = 0,
        string $primaryKey = 'ID',
        string $pattern = '[{index}]'
    ): array {
        $limit -= 50;
        $filters = isset($data['filter'][">$primaryKey"])
            ? []
            : ["q0" => $startId];

        for ($i = 0; $limit > 0; $i++) {
            $idx = min(50, $limit);
            $limit -= 50;

            $filters['q'.($i+1)] = '$result'."[q$i]".str_replace('{index}', $idx - 1, $pattern)."[$primaryKey]";
        }

        return $filters;
    }
}