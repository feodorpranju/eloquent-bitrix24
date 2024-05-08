<?php


namespace Pranju\Bitrix24\Core;


use Illuminate\Support\Facades\Http;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Batch as BatchInterface;
use Pranju\Bitrix24\Contracts\Client as ClientInterface;
use Pranju\Bitrix24\Contracts\Command as CommandInterface;
use Pranju\Bitrix24\Contracts\Repositories\Repository;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse as BatchResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\ListResponse as ListResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\Response as ResponseInterface;
use Pranju\Bitrix24\Contracts\Token;
use Pranju\Bitrix24\Core\Auth\Webhook;
use Pranju\Bitrix24\Fabrics\RepositoryFabric;
use Pranju\Bitrix24\Fabrics\ResponseFabric;
use Pranju\Bitrix24\Traits\HasStaticMake;

/**
 * Class Client
 * @package Pranju\Bitrix24\Core
 *
 * @method static static make(string|Token $token, ?string $connectionName = null)
 */
class Client implements ClientInterface
{
    use HasStaticMake;

    /**
     * Auth Token
     *
     * @var Token
     */
    protected Token $token;

    /**
     * Repository factory
     *
     * @var RepositoryFabric
     */
    protected RepositoryFabric $repositoryFabric;

    /**
     * @param string|Token $token Auth Token
     * @param string|null $connectionName Eloquent connection name
     */
    public function __construct(string|Token $token, protected ?string $connectionName = null)
    {
        $this->token = is_string($token)
            ? new Webhook($token)
            : $token;
    }

    /**
     * Calls method
     *
     * @param string $method
     * @param array $data
     * @param CommandInterface|null $command
     * @return ResponseInterface|ListResponseInterface|BatchResponseInterface
     */
    public function call(
        string $method,
        array $data = [],
        ?CommandInterface $command = null
    ): ResponseInterface|ListResponseInterface|BatchResponseInterface
    {
        return ResponseFabric::make(
            Http::asJson()->post($this->getMethodUrl($method), $data),
            ($command ?? $this->cmd($method, $data)),
        );
    }

    /**
     * Returns url for action
     *
     * @param string $method
     * @return string
     */
    protected function getMethodUrl(string $method): string
    {
        return $this->token->getUrl().$method.'.json';
    }

    /**
     * @inheritDoc
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     * @throws Bitrix24Exception
     */
    public function getRepository(string $table): Repository
    {
        $this->repositoryFabric ??= new RepositoryFabric($this);

        return $this->repositoryFabric->make($table);
    }

    /**
     * @inheritDoc
     */
    public function cmd(string $method, array $data): CommandInterface
    {
        return new Cmd($method, $data, $this);
    }

    /**
     * @inheritDoc
     */
    public function batch(array $commands, bool $halt = true): BatchInterface
    {
        return new Batch($commands, $this, $halt);
    }

    /**
     * @inheritDoc
     */
    public function getConnectionName(): string
    {
        return $this->connectionName ?? 'undefined';
    }
}