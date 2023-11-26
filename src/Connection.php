<?php

namespace Feodorpranju\Eloquent\Bitrix24;

use Composer\InstalledVersions;
use Feodorpranju\Eloquent\Bitrix24\Core\Authorization\Webhook;
use Feodorpranju\Eloquent\Bitrix24\Concerns\ManagesTransactions;
use Illuminate\Database\Connection as BaseConnection;
use JetBrains\PhpStorm\Pure;
use Feodorpranju\Eloquent\Bitrix24\Core\Client;
use Throwable;

/**
 * Class Connection
 * @package Feodorpranju\Eloquent\Bitrix24
 * @mixin Client
 */
class Connection extends BaseConnection
{
    use ManagesTransactions;

    private static ?string $version = null;

    /**
     * The MongoDB connection handler.
     *
     * @var Client
     */
    protected Client $connection;

    /**
     * Create a new database connection instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->connection = $this->createConnection($config);

        $this->useDefaultPostProcessor();

        $this->useDefaultSchemaGrammar();

        $this->useDefaultQueryGrammar();
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param string $collection
     *
     * @return Query\Builder
     */
    public function collection(string $collection): Query\Builder
    {
        $query = new Query\Builder($this, $this->getQueryGrammar(), $this->getPostProcessor());

        return $query->from($collection);
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param  string      $table
     * @param  string|null $as
     *
     * @return Query\Builder
     */
    public function table($table, $as = null): Query\Builder
    {
        return $this->collection($table);
    }

    /**
     * Get a MongoDB collection.
     *
     * @param string $name
     *
     * @return Collection
     */
    public function getCollection(string $name): Collection
    {
        return new Collection($this, $this->db->selectCollection($name));
    }

    /** @inheritdoc */
    #[Pure] public function getSchemaBuilder(): Schema\Builder
    {
        return new Schema\Builder($this);
    }

    /**
     * return MongoDB object.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabaseName(): string
    {
        return $this->connection->getToken()->getSubdomain();
    }

    /**
     * Create a new MongoDB connection.
     * @param array $config
     * @return Client
     */
    protected function createConnection(array $config): Client
    {
        if (!empty($config['webhook'])) {
            return new Client(new Webhook($config['webhook']));
        } else {
            throw new \Exception('bad config');
            //TODO: add OAuth
        }
    }

    /** @inheritdoc */
    public function disconnect()
    {
        unset($this->connection);
    }

    /** @inheritdoc */
    public function getElapsedTime($start): float
    {
        return parent::getElapsedTime($start);
    }

    /** @inheritdoc */
    public function getDriverName(): string
    {
        return 'bitrix24';
    }

    /** @inheritdoc */
    #[Pure] protected function getDefaultPostProcessor(): Query\Processor
    {
        return new Query\Processor();
    }

    /** @inheritdoc */
    #[Pure] protected function getDefaultQueryGrammar(): Query\Grammar
    {
        return new Query\Grammar();
    }

    /** @inheritdoc */
    #[Pure] protected function getDefaultSchemaGrammar(): Schema\Grammar
    {
        return new Schema\Grammar();
    }

    /**
     * Dynamically pass methods to the connection.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection->$method(...$parameters);
    }

    /**
     * @return string
     */
    private static function getVersion(): string
    {
        return self::$version ?? self::lookupVersion();
    }

    /**
     * @return string
     */
    private static function lookupVersion(): string
    {
        if (class_exists(InstalledVersions::class)) {
            try {
                return self::$version = InstalledVersions::getPrettyVersion('feodorpranju/eloquent-bitrix24');
            } catch (Throwable $e) {
                // Ignore exceptions and return unknown version
            }
        }

        return self::$version = 'unknown';
    }
}
