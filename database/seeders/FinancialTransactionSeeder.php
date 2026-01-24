<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use DateTime;

class FinancialTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all category IDs and user IDs to reference them properly
        $categories = DB::table('categories')->pluck('id')->toArray();
        $users = DB::table('dataUser')->pluck('id')->toArray();
        
        $transactions = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $isIncome = rand(0, 1) == 1;
            $category = collect($categories)->filter(function($id) use ($isIncome) {
                $category = DB::table('categories')->where('id', $id)->first();
                return $category->type === ($isIncome ? 'income' : 'expense');
            })->first();
            
            // If no matching category found, pick any category of the required type
            if (!$category) {
                $category = DB::table('categories')->where('type', $isIncome ? 'income' : 'expense')->first()->id;
            }
            
            $transactions[] = [
                'user_id' => $users[array_rand($users)],
                'category_id' => $category,
                'transaction_date' => now()->subDays(rand(0, 30))->format('Y-m-d'),
                'description' => $isIncome ? 'Income transaction #' . $i : 'Expense transaction #' . $i,
                'amount' => $isIncome ? rand(1000000, 10000000) : rand(50000, 5000000),
                'type' => $isIncome ? 'income' : 'expense',
                'status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                'approved_by' => rand(0, 1) ? $users[array_rand($users)] : null,
                'approved_at' => rand(0, 1) ? now()->subDays(rand(0, 15))->format('Y-m-d H:i:s') : null,
                'rejection_reason' => rand(0, 1) ? (rand(0, 1) ? 'Invalid documentation' : 'Exceeds budget limit') : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        foreach ($transactions as $transaction) {
            DB::table('financial_transactions')->insert($transaction);
        }
    }
}
