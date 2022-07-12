<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => 'Category ' . strtoupper(fake()->randomLetter()) . fake()->randomLetter() .' '. fake()->randomNumber(1),
            'parent_id' => null
        ];
    }
}
