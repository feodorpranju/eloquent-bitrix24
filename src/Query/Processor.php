<?php

namespace Pranju\Bitrix24\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as BaseProcessor;

class Processor extends BaseProcessor
{
    /** @inheritDoc */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null): bool|int|string
    {
        $query->getConnection()->insert($sql, $values);

        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }
}
