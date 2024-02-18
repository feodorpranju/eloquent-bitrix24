<?php


namespace Feodorpranju\Eloquent\Bitrix24\Core;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories\Repository;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\Response as ResponseInterface;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\ListResponse as ListResponseInterface;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\BatchResponse as BatchResponseInterface;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Token;
use Feodorpranju\Eloquent\Bitrix24\Core\Authorization\Webhook;
use Feodorpranju\Eloquent\Bitrix24\Core\Responses\BatchResponse;
use Feodorpranju\Eloquent\Bitrix24\Core\Responses\Response;
use Feodorpranju\Eloquent\Bitrix24\Scopes\Crm\Item;
use Feodorpranju\Eloquent\Bitrix24\Traits\HasStaticMake;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Client as ClientInterface;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Command as CommandInterface;
use \Feodorpranju\Eloquent\Bitrix24\Contracts\Batch as BatchInterface;

/**
 * Class Client
 * @package Feodorpranju\Eloquent\Bitrix24\Core
 *
 * @method static static make(string|Token $token)
 */
class Client implements ClientInterface
{
    use HasStaticMake;

    public const REPOSITORY_NAMESPACE = 'Feodorpranju\\Eloquent\\Bitrix24\\Scopes';

    protected Token $token;

    public function __construct(string|Token $token)
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
    #[Pure]
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

    public function cmd(string $method, array $data): CommandInterface
    {
        return new Cmd($method, $data, $this);
    }

    public function batch(array $commands, bool $halt = true): BatchInterface
    {
        return new Batch($commands, $this, $halt);
    }
}