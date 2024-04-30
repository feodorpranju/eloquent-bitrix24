<?php

namespace Pranju\Bitrix24\Eloquent;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;
use Pranju\Bitrix24\Eloquent\Factories\Factory;

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
 * @mixin Builder
 */
class Model extends BaseModel
{
    use HasFactory;

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

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey ??= $this->getConnection()->getRepository($this->getTable())->getPrimaryKey();
    }

    /**
     * Retrieves new model factory
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return Factory::factoryForModel(get_called_class());
    }

    /**
     * Perform a model update operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performUpdate(Builder $query): bool
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (method_exists($this, 'getRequiredForUpdateAttributes')) {
            $dirty = array_replace($this->getRequiredForUpdateAttributes(), $dirty);
        }

        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query)->update($dirty);

            $this->syncChanges();

            $this->fireModelEvent('updated', false);
        }

        return true;
    }
}