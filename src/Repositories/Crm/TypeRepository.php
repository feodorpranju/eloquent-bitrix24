<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Illuminate\Support\Arr;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Responses\BatchResponse;

class TypeRepository extends AbstractCrmRepository
{
    /**
     * @inheritDoc
     */
    public function makeCreateCommand(array $attributes, ?array $options = null): Command
    {
        return $this->cmd('add', ['fields' => $attributes]);
    }

    /**
     * @inheritDoc
     */
    public function makeUpdateCommand(int|string $id, array $attributes, ?array $options = null): Command
    {
        return $this->cmd('update', [
            'id' => $id,
            'fields' => $attributes,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return parent::getSelectedItems($response);
        }

        return (array)$response->result('types', []);
    }

    /**
     * @inheritDoc
     */
    public function getReceivedItemAttributes(Response $response): array
    {
        return (array)$response->result('type', []);
    }
}