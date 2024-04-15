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

abstract class AbstractCrmRepository extends AbstractRepository implements CanCreateItem, CanGetItem, CanDeleteItem, CanSelectItems, CanUpdateItem
{
    /**
     * @inheritDoc
     */
    public function create(array $attributes, ?array $options = null): int|string
    {
        return $this->getCreatedItemId(
            $this->makeCreateCommand($attributes, $options)->call()
        );
    }

    /**
     * @inheritDoc
     */
    public function makeCreateCommand(array $attributes, ?array $options = null): Command
    {
        return $this->cmd('add', [
            'fields' => $attributes,
            'params' => $options ?? [],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedItemId(Response $response): int|string
    {
        return $response->result();
    }

    /**
     * @inheritDoc
     */
    public function delete(int|string $id): bool
    {
        return $this->deletedSuccessfully(
            $this->makeDeleteCommand($id)->call()
        );
    }

    /**
     * @inheritDoc
     */
    public function makeDeleteCommand(int|string $id): Command
    {
        return $this->cmd(
            'delete',
            ['id' => $id],
        );
    }

    /**
     * @inheritDoc
     */
    public function deletedSuccessfully(Response $response): bool
    {
        return (bool)$response->result();
    }

    /**
     * @inheritDoc
     */
    public function get(int|string $id): ?array
    {
        return $this->getReceivedItemAttributes(
            $this->makeGetCommand($id)->call()
        );
    }

    /**
     * @inheritDoc
     */
    public function makeGetCommand(int|string $id): Command
    {
        return $this->cmd(
            'get',
            ['id' => $id]
        );
    }

    /**
     * @inheritDoc
     */
    public function getReceivedItemAttributes(Response $response): array
    {
        return (array)$response->result();
    }

    /**
     * @inheritDoc
     */
    public function select(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): array
    {
        return $this->getSelectedItems(
            $this->makeSelectCommand($filter, $select, $order, $offset, $limit)->call()
        );
    }

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): Command
    {
        $count = $this->count($filter);

        return (new ListCommandsGenerator())->generateBatch(
            $this->cmd(
                'list',
                [
                    'filter' => $filter,
                    'order' => $order,
                    'select' => $select,
                    'start' => $offset,
                    'limit' => $limit,
                ]
            ),
            min($count, $limit ?: $count),
        );
    }

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return collect($response->responses())
                ->map(fn(Response $response) => $response->result())
                ->flatten(1)
                ->all();
        }

        return (array)$response->result();
    }

    /**
     * @inheritDoc
     */
    public function getAllColumnsSelect(): array
    {
        return ['*', 'UF_*'];
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter): int
    {
        return $this->cmd('list', ['filter' => $filter])->call()?->pagination()->total() ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function update(int|string $id, array $attributes, ?array $options = null): bool
    {
        return $this->updatedSuccessfully(
            $this->makeUpdateCommand($id, $attributes, $options)->call()
        );
    }

    /**
     * @inheritDoc
     */
    public function makeUpdateCommand(int|string $id, array $attributes, ?array $options = null): Command
    {
        return $this->cmd('update', [
            'id' => $id,
            'fields' => $attributes,
            'params' => $options ?? [],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function updatedSuccessfully(Response $response): bool
    {
        return (bool)$response->result();
    }
}