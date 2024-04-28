<?php

namespace Pranju\Bitrix24\Repositories\Traits;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

trait UpdatesItem
{


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
    public function updatedSuccessfully(Response $response): bool
    {
        return $response->successful();
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
}