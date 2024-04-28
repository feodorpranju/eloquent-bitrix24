<?php

namespace Pranju\Bitrix24\Repositories\Department;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\CanCreateItem;
use Pranju\Bitrix24\Contracts\Repositories\CanDeleteItem;
use Pranju\Bitrix24\Contracts\Repositories\CanGetItem;
use Pranju\Bitrix24\Contracts\Repositories\CanSelectItems;
use Pranju\Bitrix24\Contracts\Repositories\CanUpdateItem;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Helpers\ListCommandsGenerator;
use Pranju\Bitrix24\Repositories\AbstractRepository;
use Pranju\Bitrix24\Repositories\Traits\CreatesItem;
use Pranju\Bitrix24\Repositories\Traits\DeletesItem;
use Pranju\Bitrix24\Repositories\Traits\GetsItem;
use Pranju\Bitrix24\Repositories\Traits\SelectsItems;
use Pranju\Bitrix24\Repositories\Traits\UpdatesItem;

class DepartmentRepository extends AbstractRepository implements CanCreateItem, CanUpdateItem, CanSelectItems, CanGetItem, CanDeleteItem
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
    protected function getItemName(): string
    {
        return 'department';
    }

    /**
     * @inheritDoc
     */
    public function makeGetCommand(int|string $id): Command
    {
        return $this->cmd('get', ['ID' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): Command
    {
        $count = $this->count($filter);

        return (new ListCommandsGenerator())->generatePaginatedBatch(
            $this->cmd(
                'get',
                [
                    'start' => $offset,
                    ...$filter
                ]
            ),
            min($count, $limit ?: $count)
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllColumnsSelect(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter): int
    {
        return $this->cmd('get', $filter)->call()?->pagination()->total() ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function makeCreateCommand(array $attributes, ?array $options = null): Command
    {
        return $this->cmd('add', $attributes);
    }

    /**
     * @inheritDoc
     */
    public function makeUpdateCommand(int|string $id, array $attributes, ?array $options = null): Command
    {
        return $this->cmd('update', array_replace($attributes, ['ID' => $id]));
    }

    /**
     * @inheritDoc
     */
    public function makeDeleteCommand(int|string $id): Command
    {
        return $this->cmd('delete', ['ID' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function getReceivedItemAttributes(Response $response): array
    {
        return $response->result(0, []);
    }
}