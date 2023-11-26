<?php


namespace Feodorpranju\Eloquent\Bitrix24\Scopes;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Client;
use \Feodorpranju\Eloquent\Bitrix24\Core\Client as BaseClient;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Scope;
use Feodorpranju\Eloquent\Bitrix24\Core\Cmd;
use Illuminate\Support\Str;

abstract class AbstractScope implements Scope
{
    public const DEFAULT_SELECT = ['*', 'UF_*', 'UTM_*'];
    public const PRIMARY_KEY = 'id';

    public function __construct(protected Client $client, protected ?string $collection = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function list(array $filter = [], int $start = 0, int $limit = 0, array $select = ['*', "UF_*"], ?array $order = null): ?array
    {
        // TODO: Implement list() method.
    }

    /**
     * @inheritDoc
     */
    public function update(int|string $id, array $fields): bool|array
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function add(array $fields): bool|int|string|array
    {
        // TODO: Implement add() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(int|string $id): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function fields(): ?array
    {
        // TODO: Implement fields() method.
    }

    /** @inheritdoc  */
    public function getDefaultSelect(): array
    {
        return static::DEFAULT_SELECT;
    }

    /**
     * Gets collection name
     *
     * @return string
     */
    public function getCollection(): string
    {
        if ($this->collection) {
            return $this->collection;
        }

        $class = Str::replaceStart(BaseClient::SCOPE_NAMESPACE."\\", '', static::class);
        return Str::replace("\\", '.', Str::lower($class));
    }

    /** @inheritdoc  */
    public function getPrimaryKey(): int|string|null
    {
        return static::PRIMARY_KEY;
    }

    /** @inheritdoc */
    public function cmd(string $action, array $data = [], bool $hasCollectionName = false): Cmd
    {
        $action = $hasCollectionName
            ? $this->getCollection().'.'.$action
            : $action;

        return Cmd::make($action, $data, $this->client);
    }
}