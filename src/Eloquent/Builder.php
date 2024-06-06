<?php

namespace Pranju\Bitrix24\Eloquent;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Pranju\Bitrix24\Contracts\Command;

class Builder extends \Illuminate\Database\Eloquent\Builder
{
    /**
     * Retrieves select command
     *
     * @return Command
     */
    public function toCmd(): Command
    {
        return $this->query->toCmd();
    }

    public function find($id, $columns = ['*'])
    {
        $attributes = $this->query->find($id, $columns);

        return $this->getModel()->newInstance($attributes, true);
    }

    /**
     * Inserts items and gets as array
     *
     * @param array $items
     * @return EloquentCollection
     */
    public function insertAndGet(array $items): EloquentCollection
    {
        $items = array_values($items);

        return EloquentCollection::make(
            $this->query->insertAndGetIds($items),
        )->map(
            fn($id, $key) => $this->newModelInstance(
                array_merge(
                    $items[$key],
                    [$this->model->getQualifiedKeyName() => $id]
                )
            )
        );
    }
}