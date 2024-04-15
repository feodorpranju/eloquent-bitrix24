<?php

namespace Pranju\Bitrix24\Eloquent\Factories\Crm;

use Pranju\Bitrix24\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return [
            'TITLE' => fake()->name(),
            'COMMENTS' => fake()->sentence(),
            'PHONE' => [
                ['VALUE' => fake()->phoneNumber()]
            ],
            'EMAIL' => [
                ['VALUE' => fake()->email()]
            ],
        ];
    }
}