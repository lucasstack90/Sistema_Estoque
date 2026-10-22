<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 50, 3000);

        return [
            'sku' => strtoupper(fake()->unique()->bothify('ELE-####-??')),
            'name' => fake()->words(3, true),
            'brand' => fake()->randomElement(['Samsung', 'Apple', 'Xiaomi', 'Dell', 'JBL']),
            'category_id' => Category::factory(),
            'cost_price' => $cost,
            'sale_price' => round($cost * 1.4, 2),
            'min_stock' => 5,
            'active' => true,
        ];
    }

    public function withStock(int $stock): static
    {
        return $this->afterCreating(function (Product $product) use ($stock) {
            $product->forceFill(['stock' => $stock])->save();
        });
    }
}
