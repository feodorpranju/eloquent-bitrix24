<?php


namespace Pranju\Bitrix24\Scopes\Crm;


use Pranju\Bitrix24\Contracts\Client;
use Pranju\Bitrix24\Core\Cmd;
use Illuminate\Support\Str;

class Item extends AbstractCrmScope
{
    public function __construct(protected Client $client, protected ?string $collection = null)
    {
        parent::__construct($this->client, $this->collection);

        if (!$this->collection) {
            return;
        }

        $this->collection = Str::replaceMatches('/\.\d+/', '', $this->collection);
        $this->dynamicId = (int)Str::after($collection, $this->collection.'.');
    }

    /** @inheritdoc  */
    public function getCollection(): string
    {
        return parent::getCollection().'.'.$this->getDynamicId();
    }

    public function cmd(string $action, array $data = [], bool $hasCollectionName = false): Cmd
    {
        //to get collection name without dynamic id
        $action = $hasCollectionName
            ? parent::getCollection().'.'.$action
            : $action;

        $data['entityTypeId'];

        return parent::cmd($action, $data, true);
    }
}