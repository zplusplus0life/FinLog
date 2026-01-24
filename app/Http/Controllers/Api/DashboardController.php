<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FinancialTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $data = [
            'user' => $user,
            'categories' => Category::where('is_active', true)->get(),
            'recent_transactions' => FinancialTransaction::with('category')
                ->when($user->role === 'staff', function($query) use ($user) {
                    return $query->where('user_id', $user->id);
                })
                ->latest()
                ->limit(10)
                ->get(),
            'summary' => [
                'total_income' => FinancialTransaction::where('type', 'income')
                    ->where('status', 'approved')
                    ->sum('amount'),
                'total_expense' => FinancialTransaction::where('type', 'expense')
                    ->where('status', 'approved')
                    ->sum('amount'),
                'pending_count' => FinancialTransaction::where('status', 'pending')->count()
            ]
        ];

        return response()->json($data);
    }
}
