<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(3, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->randomFloat(2, 10, 500),
            'dimensions' => fake()->numberBetween(10, 100).' x '.fake()->numberBetween(10, 100).' x '.fake()->numberBetween(10, 100).' cm',
            'weight' => fake()->randomFloat(2, 0.1, 20).' kg',
            'color' => fake()->safeColorName(),
            'material' => fake()->randomElement(['Wood', 'Metal', 'Glass', 'Plastic', 'Fabric', 'Leather']),
            'in_stock' => fake()->boolean(80),
            'status' => 'active',
        ];
    }
}
