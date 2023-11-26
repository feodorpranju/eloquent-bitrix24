<?php


namespace Feodorpranju\Eloquent\Bitrix24\Contracts;


interface Scope
{
    public function __construct(Client $client, ?string $collection = null);

    /**
     * Gets item
     *
     * @param int|string $id
     * @return array|null
     */
    public function get(int|string $id): ?array;

    /**
     * Selects item
     *
     * @param array $select
     * @param array $filter
     * @param int $start
     * @param int $limit
     * @param ?array $order
     * @return array|null
     */
    public function list(array $filter = [], int $start = 0, int $limit = 0, array $select = ['*', "UF_*"], array $order = null): ?array;

    /**
     * Updates item
     *
     * @param int|string $id
     * @param array $fields
     * @return bool|array
     */
    public function update(int|string $id, array $fields): bool|array;

    /**
     * Creates item
     *
     * @param array $fields
     * @return bool|int|string|array
     */
    public function add(array $fields): bool|int|string|array;

    /**
     * Deletes item
     *
     * @param int|string $id
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Gets item fields info
     *
     * @return array|null
     */
    public function fields(): ?array;

    /**
     * Retrns default entity select
     *
     * @return array
     */
    public function getDefaultSelect(): array;

    /**
     * Gets collection name
     *
     * @return string
     */
    public function getCollection(): string;

    /**
     * Returns entity primary key
     *
     * @return int|string|null
     */
    public function getPrimaryKey(): int|string|null;

    /**
     * Generates command with current client
     *
     * @param string $action
     * @param array $data
     * @param bool $hasCollectionName Collection name in action indicator. Prepends if false;
     * @return Command
     */
    public function cmd(string $action, array $data = [], bool $hasCollectionName = false): Command;
}