<?php

namespace Pranju\Bitrix24\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\CanCreateItem;
use Pranju\Bitrix24\Contracts\Repositories\CanDeleteItem;
use Pranju\Bitrix24\Contracts\Repositories\CanGetItem;
use Pranju\Bitrix24\Contracts\Repositories\CanSelectItems;
use Pranju\Bitrix24\Contracts\Repositories\CanUpdateItem;
use Pranju\Bitrix24\Core\Batch;

class Grammar extends BaseGrammar
{
    /**
     * @inheritDoc
     * @return Command
     * @throws Bitrix24Exception
     */
    public function compileInsertGetId(Builder $query, $values, $sequence = null): Command
    {
        $repository = $query->getConnection()->getRepository($query->from);

        if ($repository instanceof CanCreateItem) {
            return $repository->makeCreateCommand($values);
        }

        throw new Bitrix24Exception($query->from.' has no create action');
    }

    /**
     * @inheritDoc
     * @return Command
     * @throws Bitrix24Exception
     */
    public function compileUpdate(Builder $query, array $values): Command
    {
        $repository = $query->getRepository();

        $id = $query->getIdFromWheres();

        if (is_null($id)) {
            //TODO: add batch update selected ids instead
            throw new Bitrix24Exception('ID is not set in update query');
        }

        if ($repository instanceof CanUpdateItem) {
            return $repository->makeUpdateCommand($id, $values);
        }

        throw new Bitrix24Exception($query->from.' has no update action');
    }

    /**
     * @inheritDoc
     * @return Command
     * @throws Bitrix24Exception
     */
    public function compileDelete(Builder $query): Command
    {
        $repository = $query->getConnection()->getRepository($query->from);

        $id = $query->getIdFromWheres();

        if (is_null($id)) {
            //TODO: add batch update selected ids instead
            throw new Bitrix24Exception('ID is not set in update query');
        }

        if ($repository instanceof CanDeleteItem) {
            return $repository->makeDeleteCommand($id);
        }

        throw new Bitrix24Exception($query->from.' has no delete action');
    }

    /**
     * @inheritDoc
     * @return Command
     * @throws Bitrix24Exception
     */
    public function compileExists(Builder $query): Command
    {
        return $this->compileSelect($query->clone()->limit(1));
    }

    /**
     * @inheritDoc
     * @return Batch
     * @throws Bitrix24Exception
     */
    public function compileInsert(Builder $query, array $values): Batch
    {
        $batch = new Batch();

        foreach ($values as $attributes) {
            $batch->push(
                $this->compileInsertGetId($query, $attributes)
            );
        }

        return $batch;
    }

    /**
     * @inheritDoc
     * @return Command
     * @throws Bitrix24Exception
     */
    public function compileInsertOrIgnore(Builder $query, array $values): Command
    {
        return $this->compileInsertGetId($query, $values);
    }

    /**
     * @inheritDoc
     * @return Command
     * @throws Bitrix24Exception
     */
    public function compileSelect(Builder $query): Command
    {
        $repository = $query->getRepository();

        if ($repository instanceof CanSelectItems) {
            return $repository->makeSelectCommand(
                filter: $this->compileWheres($query),
                select: $this->compileColumns($query, $query->columns),
                order: $this->compileOrders($query, $query->orders),
                offset: $this->compileOffset($query, $query->offset),
                limit: $this->compileLimit($query, $query->limit),
            );
        }

        throw new Bitrix24Exception($query->from.' has no create action');
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function compileWheres(Builder $query): array
    {
        return $this->compileWheresToArray($query);
    }

    /**
     * @inheritDoc
     */
    public function compileWheresToArray($query): array
    {
        $filters = [];

        foreach ($query->wheres as $where) {
            $filters = array_replace(
                $filters,
                $this->{"where{$where['type']}"}($query, $where)
            );
        }

        return $filters;
    }

    /**
     * @inheritDoc
     * @param Builder $query
     * @param array{column: string, operator: string, value:mixed} $where
     * @return array
     */
    protected function whereBasic(Builder $query, $where): array
    {
        return [$where['operator'].$where['column'] => $this->parameter($where['value'])];
    }

    /**
     * @inheritDoc
     * @return null[]
     */
    protected function whereNull(Builder $query, $where): array
    {
        return ['='.$where['column'] => null];
    }

    protected function whereIn(Builder $query, $where)
    {
        return [$where['operator'].$where['column'] => $this->parameter($where['value'])];
    }

    /**
     * Compiles find by ID query
     *
     * @param Builder $query
     * @param string|int $id
     * @return Command
     * @throws Bitrix24Exception
     */
    public function compileFind(Builder $query, string|int $id): Command
    {
        $repository = $query->getRepository();

        if ($repository instanceof CanGetItem) {
            return $repository->makeGetCommand($id);
        }

        throw new Bitrix24Exception($query->from.' has no get action');
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function compileOrders(Builder $query, $orders): array
    {
        return $this->compileOrdersToArray($query, $orders);
    }

    /**
     * @inheritDoc
     * @return array
     */
    protected function compileOrdersToArray(Builder $query, $orders): array
    {
        return $orders;
    }

    /**
     * @inheritDoc
     * @return string[]
     */
    protected function compileColumns(Builder $query, $columns): array
    {
        $repository = $query->getRepository();

        if ($repository instanceof CanSelectItems) {
            return $repository->getAllColumnsSelect();
        }

        return ['*', 'UF_*', 'PHONE', 'EMAIL', 'WEBSITE'];
    }

    /**
     * @inheritDoc
     * @return int
     */
    protected function compileOffset(Builder $query, $offset): int
    {
        return floor($offset / 50) * 50;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function compileLimit(Builder $query, $limit): int
    {
        $limit += $query->offset - $this->compileOffset($query, $query->offset);

        return ceil($limit / 50) * 50;
    }
}
