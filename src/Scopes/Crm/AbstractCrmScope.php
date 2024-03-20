<?php


namespace Pranju\Bitrix24\Scopes\Crm;


use Pranju\Bitrix24\Scopes\AbstractScope;

abstract class AbstractCrmScope extends AbstractScope
{
    /**
     * Dynamic id of entity
     *
     * @var int
     */
    protected int $dynamicId = 0;

    /**
     * returns entity's dynamic id
     *
     * @return int
     */
    public function getDynamicId(): int
    {
        return $this->dynamicId;
    }

    /** @inheritdoc */
    public function fields(): ?array
    {
        return @static::cmd('fields')->call()['result'];
    }

    /** @inheritdoc */
    public function get(int|string $id): ?array
    {
        return @static::cmd('get', [
            $this->getPrimaryKey() => $id
        ])->call()['result'];
    }
}