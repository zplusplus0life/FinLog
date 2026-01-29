<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\FinancialTransaction;
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

    /** @test */
    public function category_has_many_transactions_relationshiip()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create(['role' => 'staff']);

        FinancialTransaction::factory()
        ->count(3)
        ->for($user)
        ->for($category)
        ->create();

        $transaksi = $category->transactions;

        $this->assertCount(3, $transaksi);
        foreach( $transaksi as $transaction){
            $this->assertEquals($category->id, $transaction->category_id);
        }
    }

    /** @test */
    public function category_can_be_deactivated()
    {
        $category = Category::factory()->create(['is_active' => true]);

        $category->update(['is_active' => false]);

        $this->assertFalse($category->is_active);
    }

    /** @test */
    public function category_can_be_reactivated()
    {
      $category = Category::factory()->create(['is_active' => false]);
      
      $category->update(['is_active' => true]);

      $this->assertTrue($category->is_active);
    }

    /** @test */
    public function category_has_all_fillable_attributes()
    {
        $fillable = ['name', 'type', 'is_active'];

        $categoryFillable =(new Category())->getFillable();

        foreach($fillable as $attribute){
            $this->assertContains($attribute, $categoryFillable);
        }
    }

    /** @test */
    public function category_timestamp_are_auto_managed()
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->created_at);
        $this->assertNotNull($category->updated_at);
    }

    /** @test */
    public function multiple_categories_can_have_transactions()
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();
        $user = User::factory()->create(['role' => 'staff']);

        FinancialTransaction::factory()
        ->count(2)
        ->for($user)
        ->for($cat1)
        ->create();

        FinancialTransaction::factory()
        ->count(3)
        ->for($user)
        ->for($cat2)
        ->create();

        $this->assertCount(2, $cat1->transactions);
        $this->assertCount(3, $cat2->transactions);
    }

    /** @test */
    public function deleting_category_cascades_to_transactions()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create(['role' =>'staff']);

        FinancialTransaction::factory()
        ->count(2)
        ->for($user)
        ->for($category)
        ->create();

        $category->delete();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('financial_transactions', ['category_id' => $category->id]);
    }
}
