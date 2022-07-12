<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'name' => 'Product ' . strtoupper(fake()->randomLetter()) . fake()->randomLetter() . ' ' . fake()->randomNumber(2),
            'description' => fake()->text(),
            'quantity' => fake()->randomDigitNotZero(),
            'price' => fake()->randomFloat(2, 10, 999999),
            'image' => null,
            'status' => fake()->randomElement([Product::INACTIVE, Product::ACTIVE])
        ];
    }
}
