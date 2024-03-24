<?php

namespace Pranju\Bitrix24\Eloquent;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;

/**
 * \Pranju\Bitrix24\Eloquent\Model
 *
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static query()
 * @method static Builder|static where(string $param1, string $value, string $value2 = null)
 * @method static Builder|static whereId($value)
 * @method static Builder|static whereIn(mixed $fieldName, mixed $operandOrValue, mixed $value=null)
 * @method static static updateOrCreate(array $array, array $array1)
 * @method static static first()
 * @method static static[]|Collection get()
 * @method static static find(int $value)
 * @mixin Model
 */
class Model extends BaseModel
{
    /**
     * @inheritDoc
     */
    protected $primaryKey = 'ID';

    /**
     * @inheritDoc
     */
    protected $guarded = [];

    /**
     * @inheritDoc
     */
    public $timestamps = false;

    /** @inheritDoc */
    public function newEloquentBuilder($query): Builder
    {
        return new Builder($query);
    }

    /** @inheritDoc */
    public function getQualifiedKeyName(): string
    {
        return $this->getKeyName();
    }

    /** @inheritDoc */
    public function getTable(): string
    {
        return $this->table ??= Str::contains(static::class, 'Models')
            ? Str::snake(Str::replace("\\", '', Str::after(static::class, "Models\\")))
            : Str::snake(class_basename($this));
    }
}