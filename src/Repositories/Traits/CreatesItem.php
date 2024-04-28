<?php

namespace Pranju\Bitrix24\Repositories\Traits;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

trait CreatesItem
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
        return (int)$response->result();
    }
}