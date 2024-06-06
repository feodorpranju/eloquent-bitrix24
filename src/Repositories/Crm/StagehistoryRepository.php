<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Illuminate\Support\Arr;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\CanSelectItems;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Batch;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Helpers\ListCommandsGenerator;
use Pranju\Bitrix24\Repositories\AbstractRepository;
use Pranju\Bitrix24\Repositories\Traits\SelectsItems;

class StagehistoryRepository extends AbstractRepository implements CanSelectItems
{
    use SelectsItems {
        SelectsItems::getSelectedItems as baseGetSelectedItems;
    }

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null,): Command
    {
        $count = $this->count($filter);

        if ($count === 0) {
            return Batch::make([], $this->getClient());
        }

        return (new ListCommandsGenerator())->generateBatch(
            command: $this->cmd('list', [
                'entityTypeId' => $filter['entityTypeId'] ?? $filter['=entityTypeId'] ?? null,
                'filter' => Arr::except($filter, 'entityTypeId'),
                'order' => $order,
                'select' => $select,
            ]),
            limit: min($count, $limit ?: $count),
            pattern: '[items][{index}]'
        );
    }

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return $this->baseGetSelectedItems($response);
        }

        return (array)$response->result('items', []);
    }

    /**
     * @inheritDoc
     */
    public function getAllColumnsSelect(): array
    {
        return ['*'];
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter): int
    {
        return $this->cmd('list', [
            'entityTypeId' => $filter['entityTypeId'] ?? $filter['=entityTypeId'] ?? null,
            'filter' => Arr::except($filter, 'entityTypeId'),
            'select' => ['ID'],
        ])->call()?->pagination()->total() ?? 0;
    }

    /**
     * @inheritDoc
     * @throws Bitrix24Exception
     */
    public function cmd(string $action, array $data = []): Command
    {
        if (empty($data['entityTypeId'])) {
            throw new Bitrix24Exception("Param entityTypeId is required for {$this->getItemName()}.$action method");
        }

        return parent::cmd($action, $data);
    }
}