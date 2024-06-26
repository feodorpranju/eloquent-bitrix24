<?php

namespace Pranju\Bitrix24\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as BaseProcessor;
use Illuminate\Support\Arr;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Batch;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\CanCreateItem;
use Pranju\Bitrix24\Contracts\Repositories\CanGetItem;
use Pranju\Bitrix24\Contracts\Repositories\CanUpdateItem;
use Pranju\Bitrix24\Contracts\Repositories\Repository;
use Pranju\Bitrix24\Contracts\Responses\Response;

class Processor extends BaseProcessor
{
    /**
     * @param Command $sql
     * @inheritDoc
     * @throws Bitrix24Exception
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null): bool|int|string
    {
        $repository = $query->getConnection()->getRepository($query->from);

        if ($repository instanceof CanCreateItem) {
            return $repository->getCreatedItemId($sql->call());
        }

        throw new Bitrix24Exception($query->from.' has no create action');
    }

    /**
     * @param Builder $query
     * @param Batch $batch
     * @return array
     * @throws Bitrix24Exception
     */
    public function processInsertGetIds(Builder $query, Batch $batch): array
    {
        $repository = $query->getConnection()->getRepository($query->from);

        if ($repository instanceof CanCreateItem) {
            return array_map(
                fn($response) => $repository->getCreatedItemId($response),
                $batch->call()->responses()
            );
        }

        throw new Bitrix24Exception($query->from.' has no create action');
    }

    /**
     * Process update query
     *
     * @param Builder $query
     * @param Command $command
     * @return bool
     * @throws Bitrix24Exception
     */
    public function processUpdate(Builder $query, Command $command): bool
    {
        $repository = $query->getConnection()->getRepository($query->from);

        if ($repository instanceof CanUpdateItem) {
            return $repository->updatedSuccessfully($command->call());
        }

        throw new Bitrix24Exception($query->from.' has no update action');
    }

    /**
     * Process update query
     *
     * @param Builder $query
     * @param Command $command
     * @param array|string $columns
     * @return array
     * @throws Bitrix24Exception
     */
    public function processFind(Builder $query, Command $command, array|string $columns): array
    {
        $repository = $query->getConnection()->getRepository($query->from);

        if (! $repository instanceof CanGetItem) {
            throw new Bitrix24Exception($query->from.' has no get action');
        }

        $attributes = $repository->getReceivedItemAttributes($command->call());

        $columns = Arr::wrap($columns);

        if (in_array('*', $columns)) {
            $columns = [];
        }

        return empty($columns)
            ? $attributes
            : Arr::only($attributes, $columns);
    }

    /**
     * @inheritDoc
     */
    public function processSelect(Builder $query, $results)
    {
        return array_slice($results, $query->offset % 50, $query->limit);
    }
}
