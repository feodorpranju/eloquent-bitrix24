<?php


namespace Pranju\Bitrix24\Core;


use Pranju\Bitrix24\Contracts\Repositories\Repository;
use Pranju\Bitrix24\Contracts\Responses\Response as ResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\ListResponse as ListResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse as BatchResponseInterface;
use Pranju\Bitrix24\Contracts\Token;
use Pranju\Bitrix24\Core\Authorization\Webhook;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Core\Responses\Response;
use Pranju\Bitrix24\Scopes\Crm\Item;
use Pranju\Bitrix24\Traits\HasStaticMake;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use Pranju\Bitrix24\Contracts\Client as ClientInterface;
use Pranju\Bitrix24\Contracts\Command as CommandInterface;
use \Pranju\Bitrix24\Contracts\Batch as BatchInterface;

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
        $response = Http::post($this->getMethodUrl($method), $data);
        $command = $command ?? $this->cmd($method, $data);

        if ($method === 'batch') {
            return new BatchResponse($response, $command);
        }

        if (Str::endsWith($method, 'list')) {
            return new BatchResponse($response, $command);
        }

        return new Response($response, $command);
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
     */
    public function getRepository(string $collection): Repository
    {
        if (Str::startsWith($collection, 'crm.item.')) {
            return new Item($this, $collection);
        }

        $parts = explode('.', strtolower($collection));

        $class = static::SCOPE."\\".join("\\", array_map(fn($part) => ucfirst($part), $parts));

        throw_unless(class_exists($class), new \Exception("Undefined collection $collection"));

        return new $class($this, $collection);
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