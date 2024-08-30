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
    public function __construct(protected readonly string $filterField = 'filter')
    {
    }

    /**
     * Generates batch with paginated command due to limit
     *
     * @param Command $command
     * @param int $limit
     * @return BatchInterface
     */
    public function generatePaginatedBatch(Command $command, int $limit): BatchInterface
    {
        $batch = Batch::make([], $command->getClient());
        $data = $command->getData();
        $data['limit'] = 50;

        for ($start = $data['start'] ?? 0; $start < $limit; $start += 50) {
            $batch->push(Cmd::make(
                $command->getMethod(),
                array_replace($data, ['start' => $start]),
            ));
        }

        return $batch;
    }

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
        $data['limit'] = 50;
        $data['order'] = array_replace([$primaryKey => 'ASC'], $data['order'] ?? []);
        $condition = ">$primaryKey";

        return Batch::make(
            collect(
                $this->generateFilters(
                    $limit,
                    max($data[$this->filterField][$condition] ?? 0, $startId),
                    $pattern."[$primaryKey]"
                )
            )->map(
                fn ($filter, $key) => Cmd::make(
                    $command->getMethod(),
                    array_replace_recursive(
                        $data,
                        [
                            $this->filterField => $filter === 0
                                ? []
                                : [$condition => $filter],
                            'start' => $key === 'q0' ? $data['start'] ?? -1 : -1
                        ],
                    ),
                )
            )->all(),
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