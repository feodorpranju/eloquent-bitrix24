<?php

namespace Pranju\Bitrix24\Eloquent\Factories;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;
use Pranju\Bitrix24\Eloquent\Model;

abstract class Factory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    public static $namespace = __NAMESPACE__.'\\';

    protected static function appNamespace(): string
    {
        return 'Pranju\\Bitrix24\\';
    }



    /**
     * Set the connection name on the results and store them.
     *
     * @param Collection $results
     * @return void
     */
    protected function store(Collection $results): void
    {
        $createdResults = $this->modelName()::query()->insertAndGet($results->toArray());

        $results->each(
            fn($model, $key) => $model->forceFill($createdResults[$key]->toArray())->syncOriginal()
        );

        $results->each(function ($model) {
            foreach ($model->getRelations() as $name => $items) {
                if ($items instanceof Enumerable && $items->isEmpty()) {
                    $model->unsetRelation($name);
                }
            }

            $this->createChildren($model);
        });
    }
}