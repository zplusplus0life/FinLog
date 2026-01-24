<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = FinancialTransaction::with('category');
        
        // Role-based filtering
        if ($user->role === 'staff') {
            $query->where('user_id', $user->id);
        }
        
        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest()->paginate(20);
        
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense'
        ]);

        $transaction = FinancialTransaction::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => 'pending'
        ]);

        return response()->json($transaction->load('category'), 201);
    }
}
