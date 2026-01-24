<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'transaction_date' => $this->faker->date(),
            'amount' => $this->faker->numberBetween(1000, 1_000_000),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
