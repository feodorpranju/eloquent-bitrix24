<?php


namespace Feodorpranju\Eloquent\Bitrix24\Core;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Scope;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Token;
use Feodorpranju\Eloquent\Bitrix24\Core\Authorization\Webhook;
use Feodorpranju\Eloquent\Bitrix24\Scopes\Crm\Item;
use Feodorpranju\Eloquent\Bitrix24\Traits\HasStaticMake;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Client as ClientInterface;

/**
 * Class Client
 * @package Feodorpranju\Eloquent\Bitrix24\Core
 *
 * @method static static make(string|Token $token)
 */
class Client implements ClientInterface
{
    use HasStaticMake;

    public const SCOPE_NAMESPACE = 'Feodorpranju\\Eloquent\\Bitrix24\\Scopes';

    protected Token $token;

    public function __construct(string|Token $token)
    {
        $this->token = is_string($token)
            ? new Webhook($token)
            : $token;
    }

    /**
     * Calls action
     *
     * @param string $action
     * @param array $data
     * @return array
     */
    public function call(string $action, array $data = []): array
    {
        return Http::post($this->getActionUrl($action), $data)->json();
    }

    /**
     * Returns url for action
     *
     * @param string $action
     * @return string
     */
    #[Pure] public function getActionUrl(string $action): string
    {
        return $this->token->getUrl().$action.'.json';
    }

    /**
     * Returns client's token
     *
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @param string $collection
     * @return Scope
     */
    public function getScope(string $collection): Scope
    {
        if (Str::startsWith($collection, 'crm.item.')) {
            return new Item($this, $collection);
        }

        $parts = explode('.', strtolower($collection));

        $class = static::SCOPE_NAMESPACE."\\".join("\\", array_map(fn($part) => ucfirst($part), $parts));

        throw_unless(class_exists($class), new \Exception("Undefined collection $collection"));

        return new $class($this, $collection);
    }
}