<?php

namespace Tests\Unit;


use App\Models\User;
use App\Models\FinancialTransaction;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTransactionModelTest extends TestCase
{
    
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['type' => 'expense']);
    }

    /** @test */
    public function transaction_can_be_created_with_valid_data()
    {
       $transaction = FinancialTransaction::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'transaction_date' => '2024-01-15',
        'amount' => '5000',
        'description' => 'Office suplies',
        'type' => 'expense',
        'status' => 'pending',
       ]);

       $this->assertNotNull($transaction->id);
       $this->assertEquals('5000', $transaction->amount);
       $this->assertEquals('pending', $transaction->status);
    }

    /** @test */
    public function  transaction_has_belongs_to_user_relationship()
    {
        $transaction = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertEquals($this->user->id, $transaction->user->id);
    }
}
