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


    /** @test */
    public function transaction_has_belongs_to_category_relationship()
    {
        $transaction = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

        $this->assertInstanceOf(Category::class, $this->category);
        $this->assertEquals($this->category->id, $transaction->category->id);
    }

    /** @test */
    public function transaction_can_store_income_and_expense_types()
    {
        $income = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['type' => 'income']);

        $expense = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['type' => 'expense']);

        $this->assertEquals('income', $income->type);
        $this->assertEquals('expense', $expense->type);
    }

    /** @test */
    public function transaction_can_store_all_status_values()
    {
        $status = ['pending', 'approved', 'rejected'];

        foreach($status as $data){
            $transaction = FinancialTransaction::factory()
            ->for($this->user)
            ->for($this->category)
            ->create(['status' => $data]);

            $this->assertEquals($data, $transaction->status);
        }
    }

    /** @test */
    public function transaction_can_store_large_amounts()
    {
        $transaction = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['amount' => 9999999999999.99]);

        $this->assertEquals(9999999999999.99, $transaction->amount);
    }


    /** @test */
    public function transaction_can_store_rejection_reason()
    {
        $transaction = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['rejection_reason' => 'Amount exceeds limit']);

        $this->assertEquals('Amount exceeds limit', $transaction->rejection_reason);
    }

    /** @test */
    public function transaction_can_store_approved_by_id()
    {
        $manajer = User::factory()->create(['role' => 'manajer']);
        $transaction = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['approved_by' => $manajer->id]);

        $this->assertEquals($manajer->id, $transaction->approved_by);
    }

    /** @test */
    public function transaction_can_store_approved_at_timestamp()
    {
        $now = now();
        $transaction = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['approved_at' => $now]);

        $this->assertEquals($now, $transaction->approved_at);
    }
    /** @test */
    public function transaction_has_all_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'category_id',
            'transaction_date',
            'amount',
            'description',
            'type',
            'status',
        ];

        $transactionFillable = (new FinancialTransaction())->getFillable();
        foreach($fillable as $attribute){
            $this->assertContains($attribute, $transactionFillable);
        }
    }

    /** @test */
    public function transaction_timestamps_are_auto_managed()
    {
        $transaction = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

        $this->assertNotNull($transaction->created_at);
        $this->assertNotNull($transaction->updated_at);
    }

    /** @test */
    public function multiple_transaction_can_belong_to_same_user()
    {
        $trans1= FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

        $trans2= FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

            $this->assertEquals($this->user->id, $trans1->user_id);
    $this->assertEquals($this->user->id, $trans2->user_id);
    }


    /** @test */
    public function multiple_transaction_can_belong_to_same_category()
    {
        $trans1 = FinancialTransaction::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

        $trans2 = FinancialTransaction::factory()
        ->for(User::factory()->create(['role' => 'staff']))
        ->for($this->category)
        ->create();

        $this->assertEquals($this->category->id, $trans1->category_id);
        $this->assertEquals($this->category->id, $trans2->category_id);
    }

}
