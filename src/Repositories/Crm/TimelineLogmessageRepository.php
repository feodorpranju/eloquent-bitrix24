<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\CanCreateItem;
use Pranju\Bitrix24\Contracts\Repositories\CanDeleteItem;
use Pranju\Bitrix24\Contracts\Repositories\CanGetItem;
use Pranju\Bitrix24\Contracts\Repositories\CanSelectItems;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Helpers\ListCommandsGenerator;
use Pranju\Bitrix24\Repositories\AbstractRepository;
use Pranju\Bitrix24\Repositories\Traits\CreatesItem;
use Pranju\Bitrix24\Repositories\Traits\DeletesItem;
use Pranju\Bitrix24\Repositories\Traits\GetsItem;
use Pranju\Bitrix24\Repositories\Traits\SelectsItems;

class TimelineLogmessageRepository extends AbstractRepository implements CanCreateItem, CanGetItem, CanSelectItems, CanDeleteItem
{
    use GetsItem, SelectsItems, CreatesItem, DeletesItem {
        SelectsItems::getSelectedItems as baseGetSelectedItems;
    }

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): Command
    {
        return (new ListCommandsGenerator())->generatePaginatedBatch(
            $this->cmd('list', [
                'entityTypeId' => $filter['entityTypeId'] ?? $filter['=entityTypeId'] ?? null,
                'entityId' => $filter['entityId'] ?? $filter['=entityId'] ?? null,
            ]),
            $this->count($filter)
        );
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter): int
    {
        return $this->cmd('list', [
            'entityTypeId' => $filter['entityTypeId'] ?? $filter['=entityTypeId'] ?? null,
            'entityId' => $filter['entityId'] ?? $filter['=entityId'] ?? null,
        ])->call()->pagination()->total() ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return $this->baseGetSelectedItems($response);
        }

        return (array)$response->result('logMessages', []);
    }
}