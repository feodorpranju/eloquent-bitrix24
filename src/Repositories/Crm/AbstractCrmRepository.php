<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\CanCreateItem;
use Pranju\Bitrix24\Contracts\Repositories\CanDeleteItem;
use Pranju\Bitrix24\Contracts\Repositories\CanGetItem;
use Pranju\Bitrix24\Contracts\Repositories\CanSelectItems;
use Pranju\Bitrix24\Contracts\Repositories\CanUpdateItem;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Cmd;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Helpers\ListCommandsGenerator;
use Pranju\Bitrix24\Repositories\AbstractRepository;
use Pranju\Bitrix24\Repositories\Traits\CreatesItem;
use Pranju\Bitrix24\Repositories\Traits\DeletesItem;
use Pranju\Bitrix24\Repositories\Traits\GetsItem;
use Pranju\Bitrix24\Repositories\Traits\SelectsItems;
use Pranju\Bitrix24\Repositories\Traits\UpdatesItem;

abstract class AbstractCrmRepository extends AbstractRepository implements CanCreateItem, CanGetItem, CanDeleteItem, CanSelectItems, CanUpdateItem
{
    use
        CreatesItem,
        UpdatesItem,
        GetsItem,
        SelectsItems,
        DeletesItem;

    /**
     * @inheritDoc
     */
    public function getAllColumnsSelect(): array
    {
        return ['*', 'UF_*'];
    }
}