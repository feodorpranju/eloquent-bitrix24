<?php

namespace Pranju\Bitrix24\Repositories\Tasks;

use Illuminate\Support\Str;
use Pranju\Bitrix24\Contracts\Repositories\CanCreateItem;
use Pranju\Bitrix24\Contracts\Repositories\CanDeleteItem;
use Pranju\Bitrix24\Contracts\Repositories\CanGetItem;
use Pranju\Bitrix24\Contracts\Repositories\CanSelectItems;
use Pranju\Bitrix24\Contracts\Repositories\CanUpdateItem;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Repositories\AbstractRepository;
use Pranju\Bitrix24\Repositories\Traits\CreatesItem;
use Pranju\Bitrix24\Repositories\Traits\DeletesItem;
use Pranju\Bitrix24\Repositories\Traits\GetsItem;
use Pranju\Bitrix24\Repositories\Traits\SelectsItems;
use Pranju\Bitrix24\Repositories\Traits\UpdatesItem;

class AbstractTasksRepository extends AbstractRepository implements CanGetItem, CanSelectItems, CanDeleteItem, CanUpdateItem, CanCreateItem
{
    use GetsItem, CreatesItem, UpdatesItem, DeletesItem, SelectsItems;

    /**
     * Converts attributes keys to screaming snake case
     *
     * @param array $attributes
     * @return array
     */
    protected function attributesToScreaming(array $attributes): array
    {
        $newAttributes = [];

        foreach ($attributes as $key => $attribute) {
            $newAttributes[Str::screaming($key)] = $attribute;
        }

        return $newAttributes;
    }
}