<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 *
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        User::factory(10)->create();

        $parentCategories = Category::factory(100)->create();
        $parentCategoriesIds = $parentCategories->pluck('id')->toArray();

        $lvl1Categories = Category::factory(1)->create([
            'name' => 'Category ' . strtoupper(fake()->randomLetter()) . fake()->randomLetter() . ' ' . fake()->randomNumber(2),
            'parent_id' => fake()->randomElement($parentCategoriesIds)
        ]);

        for ($i = 1; $i <= 199; $i++) {
            $lvl1Categories = $lvl1Categories->merge(Category::factory(1)->create([
                'name' => 'Category ' . strtoupper(fake()->randomLetter()) . fake()->randomLetter() . ' ' . fake()->randomNumber(2),
                'parent_id' => fake()->randomElement($parentCategoriesIds)
            ]));
        }

        $lvl1CategoriesIds = $lvl1Categories->pluck('id')->toArray();

        for ($i = 1; $i <= 400; $i++) {
            Category::factory(1)->create([
                'name' => 'Category ' . strtoupper(fake()->randomLetter()) . fake()->randomLetter() . ' ' . fake()->randomNumber(3),
                'parent_id' => fake()->randomElement($lvl1CategoriesIds)
            ]);
        }

        Product::factory(5000)->create();
    }
}
