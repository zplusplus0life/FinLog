<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
     /** @test */
    public function category_can_be_created_with_valid_data()
    {
        $category = Category::create([
            'name' => 'Office Supplies',
            'type' => 'expense',
            'is_active' => true,
        ]);

        $this->assertNotNull($category->id);
        $this->assertEquals('Office Supplies', $category->name);
        $this->assertEquals('expense', $category->type);
        $this->assertTrue($category->is_active);
    }

        /** @test */
    public function category_defaults_is_active_to_true()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'type' => 'income',
        ]);

        $this->assertTrue($category->is_active);
    }


        /** @test */
    public function category_can_store_income_and_expense_types()
    {
        $income = Category::factory()->create(['type' => 'income']);
        $expense = Category::factory()->create(['type' => 'expense']);

        $this->assertEquals('income', $income->type);
        $this->assertEquals('expense', $expense->type);
    }
}
