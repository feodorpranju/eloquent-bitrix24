<?php

namespace Pranju\Bitrix24\Repositories\Tasks;

use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Responses\BatchResponse;

class TaskRepository extends AbstractTasksRepository
{
    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return parent::getSelectedItems($response);
        }

        return array_map(
            fn($attributes) => $this->attributesToScreaming($attributes),
            $response->result('tasks', [])
        );
    }

    /**
     * @inheritDoc
     */
    public function getReceivedItemAttributes(Response $response): array
    {
        return $this->attributesToScreaming(
            (array)$response->result('task', [])
        );
    }
}