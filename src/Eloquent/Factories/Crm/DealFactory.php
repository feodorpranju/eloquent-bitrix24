<?php

namespace Pranju\Bitrix24\Eloquent\Factories\Crm;

use Pranju\Bitrix24\Eloquent\Factories\Factory;

class DealFactory extends Factory
{

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return [
            'TITLE' => fake()->name(),
            'COMMENTS' => fake()->sentence(),
        ];
    }
}