<?php

namespace Database\Factories;

use App\Models\ContractTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractTemplateFactory extends Factory
{
    protected $model = ContractTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'content' => '<p>Hola <span data-token="nombre">___</span></p>',
            'type' => 'full',
            'is_default'  => false,
            'user_id' => null,
            'preview_path' => null,
        ];
    }
}
