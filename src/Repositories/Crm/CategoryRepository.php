<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Illuminate\Support\Arr;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Responses\BatchResponse;

class CategoryRepository extends AbstractCrmRepository
{
    /**
     * @inheritDoc
     */
    public function makeGetCommand(int|string $id): Command
    {
        [$entityTypeId, $id] = explode(':', $id);

        return $this->cmd('get', [
            'entityTypeId' => $entityTypeId,
            'id' => $id
        ]);
    }

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): Command
    {
        return $this->cmd('list', [
            'entityTypeId' => $filter['entityTypeId'] ?? $filter['=entityTypeId'] ?? null,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function makeCreateCommand(array $attributes, ?array $options = null): Command
    {
        return $this->cmd('add', [
            'entityTypeId' => Arr::get($attributes, 'entityTypeId'),
            'fields' => Arr::except($attributes, 'entityTypeId'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function makeUpdateCommand(int|string $id, array $attributes, ?array $options = null): Command
    {
        return $this->cmd('update', [
            'id' => $id,
            'entityTypeId' => Arr::get($attributes, 'entityTypeId'),
            'fields' => Arr::except($attributes, 'entityTypeId'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function makeDeleteCommand(int|string $id): Command
    {
        [$entityTypeId, $id] = explode(':', $id);

        return $this->cmd('delete', [
            'entityTypeId' => $entityTypeId,
            'id' => $id
        ]);
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

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return parent::getSelectedItems($response);
        }

        return (array)$response->result('categories', []);
    }

    /**
     * @inheritDoc
     */
    public function getReceivedItemAttributes(Response $response): array
    {
        return (array)$response->result('category', []);
    }
}