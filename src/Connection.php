<?php

namespace Pranju\Bitrix24;

use Composer\InstalledVersions;
use Pranju\Bitrix24\Core\Authorization\Webhook;
use Illuminate\Database\Connection as BaseConnection;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Pranju\Bitrix24\Core\Client;
use Throwable;
use Pranju\Bitrix24\Contracts\Client as ClientInterface;

/**
 * Class Connection
 * @package Pranju\Bitrix24
 * @mixin ClientInterface
 * @method \Pranju\Bitrix24\Contracts\Repositories\Repository getRepository(string $table)
 */
class Connection extends BaseConnection
{

    private static ?string $version = null;

    /**
     * The MongoDB connection handler.
     *
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * Create a new database connection instance.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->validateConfig($this->config);

        $this->client = $this->createClient();

        $this->useDefaultPostProcessor();

        $this->useDefaultSchemaGrammar();

        $this->useDefaultQueryGrammar();
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param  string      $table Table name
     * @param  string|null $as Unused
     *
     * @return Query\Builder
     */
    public function table($table, $as = null): Query\Builder
    {
        return $this->query()->from($table);
    }

    /** @inheritDoc */
    public function query(): Query\Builder
    {
        return new Query\Builder($this, $this->getQueryGrammar(), $this->getPostProcessor());
    }

    /** @inheritdoc */
    public function getSchemaBuilder(): Schema\Builder
    {
        return new Schema\Builder($this);
    }

    /**
     * Return Bitrix24 client.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabaseName(): string
    {
        return $this->client->getToken()->getHost();
    }

    /**
     * Create a new Bitrix24 connection.
     * @return Client
     */
    protected function createClient(): Client
    {
        $client = match ($this->getConfig('type')) {
            'webhook' => Client::make(
                new Webhook($this->getConfig('webhook')),
                $this->getName()
            ),
            default => null,
        };

        if (!$client) {
            throw new InvalidArgumentException('Database is not properly configured.');
        }

        return $client;
    }

    /**
     * @param array $config
     * @throws InvalidArgumentException
     */
    protected function validateConfig(array $config): void
    {
        $exception = new InvalidArgumentException('Database is not properly configured.');

        switch ($config['type'] ?? '') {
            case 'webhook':
                if (!empty($config['webhook'])) {
                    return;
                }
                break;
            case 'oauth':
                if (
                    !empty($config['client_id'])
                    && !empty($config['client_secret'])
                    && !empty($config['host'])
                ) {
                    return;
                }
                break;
            case 'placement':
                break;
        }

        throw $exception;
    }

    /** @inheritdoc */
    public function disconnect()
    {
        unset($this->client);
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
        return $this->client->$method(...$parameters);
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
