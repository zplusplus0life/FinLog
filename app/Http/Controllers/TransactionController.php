<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        $transactions = FinancialTransaction::with('category')
                                        ->where('user_id', $user->id)
                                        ->latest()
                                        ->get();

        return Inertia::render('dashboard/staff/Manage', [
            'categories' => Category::all(),
            'transactions' => $transactions,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load user and category relationships
        return FinancialTransaction::with(['user', 'category'])
                                    ->latest()
                                    ->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
        ]);

        $transaction = FinancialTransaction::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'transaction_date' => $validated['transaction_date'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'status' => 'pending', // Always pending on creation
        ]);

        // Eager load the category relationship to include it in the response
        $transaction->load('category');

        return response()->json($transaction, 201);
    }

    /**
     * Get overview statistics.
     */
    public function overview()
    {
        $approved = FinancialTransaction::where('status', 'approved');

        $income_total = (clone $approved)->where('type', 'income')->sum('amount');
        $expense_total = (clone $approved)->where('type', 'expense')->sum('amount');

        $pending_transactions = FinancialTransaction::where('status', 'pending')->count();

        return response()->json([
            'income_total' => $income_total,
            'expense_total' => $expense_total,
            'net_profit' => $income_total - $expense_total,
            'pending_transactions' => $pending_transactions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinancialTransaction $transaction)
    {
        // Authorize: Only the owner can update pending transactions
        if ($transaction->user_id !== Auth::id() || $transaction->status !== 'pending') {
            return response()->json(['message' => 'Unauthorized or transaction not editable.'], 403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
        ]);

        $transaction->update($validated);

        // Load the category relationship before returning
        $transaction->load('category');

        return response()->json($transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialTransaction $transaction)
    {
        // Authorize: Only the owner can delete pending transactions
        if ($transaction->user_id !== Auth::id() || $transaction->status !== 'pending') {
            return response()->json(['message' => 'Unauthorized or transaction not deletable.'], 403);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully.'], 204);
    }

    /**
     * Get all pending transactions for manager verification.
     */
    public function pending()
    {
        // Eager load relationships needed by the verification UI
        $pending = FinancialTransaction::with(['user', 'category'])
                                       ->where('status', 'pending')
                                       ->latest()
                                       ->get();
        return response()->json($pending);
    }

    /**
     * Approve a pending transaction.
     */
    public function approve(FinancialTransaction $transaction)
    {
        // Basic validation: only pending transactions can be approved
        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Transaction is not pending and cannot be approved.'], 422); // Unprocessable Entity
        }

        $transaction->status = 'approved';
        $transaction->rejection_reason = null; // Clear any previous rejection reason
        $transaction->approved_by = Auth::id();
        $transaction->approved_at = Carbon::now();
        $transaction->save();

        return response()->json($transaction);
    }

    /**
     * Reject a pending transaction.
     */
    public function reject(Request $request, FinancialTransaction $transaction)
    {
        // Basic validation: only pending transactions can be rejected
        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Transaction is not pending and cannot be rejected.'], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $transaction->status = 'rejected';
        $transaction->rejection_reason = $validated['rejection_reason'];
        $transaction->save();

        return response()->json($transaction);
    }

    /**
     * Generate a financial report grouped by category.
     */
    public function getFinancialReport(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        // --- Get transactions for the selected period and all-time ---
        $periodQuery = FinancialTransaction::with('category')->where('status', 'approved')->whereYear('transaction_date', $validated['year']);
        if (!empty($validated['month'])) {
            $periodQuery->whereMonth('transaction_date', $validated['month']);
        }
        $periodTransactions = $periodQuery->get();
        $allTransactions = FinancialTransaction::where('status', 'approved')->get();

        // --- Define Keyword-based Chart of Accounts ---
        $cogsKeywords = ['hpp', 'bahan baku', 'cost of sales', 'inventory'];
        $smKeywords = ['pemasaran', 'iklan', 'promosi', 'sales', 'marketing'];
        $investingKeywords = ['aset', 'properti', 'peralatan', 'investasi'];
        $financingKeywords = ['pinjaman', 'modal', 'saham', 'dividen'];

        // --- (1) INCOME STATEMENT LOGIC ---
        $revenueTransactions = $periodTransactions->where('type', 'income')->diff($periodTransactions->filter(fn($t) => $this->categoryContains($t, $financingKeywords)));
        $expenseTransactions = $periodTransactions->where('type', 'expense');
        
        $cogsTransactions = $expenseTransactions->filter(fn($t) => $this->categoryContains($t, $cogsKeywords));
        $smTransactions = $expenseTransactions->diff($cogsTransactions)->filter(fn($t) => $this->categoryContains($t, $smKeywords));
        $gaTransactions = $expenseTransactions->diff($cogsTransactions)->diff($smTransactions)->filter(fn($t) => !$this->categoryContains($t, $investingKeywords) && !$this->categoryContains($t, $financingKeywords));

        $totalRevenue = $revenueTransactions->sum('amount');
        $totalCogs = $cogsTransactions->sum('amount');
        $grossProfit = $totalRevenue - $totalCogs;
        $totalSmExpenses = $smTransactions->sum('amount');
        $totalGaExpenses = $gaTransactions->sum('amount');
        $totalOperatingExpenses = $totalSmExpenses + $totalGaExpenses;
        $netProfit = $grossProfit - $totalOperatingExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // --- (2) SIMPLIFIED BALANCE SHEET LOGIC ---
        $totalCash = $allTransactions->where('type', 'income')->sum('amount') - $allTransactions->where('type', 'expense')->sum('amount');
        $totalEquity = $totalCash;

        // --- (3) 3-SECTION CASH FLOW LOGIC ---
        $cfOperatingInflows = $revenueTransactions->sum('amount');
        $cfOperatingOutflows = $cogsTransactions->sum('amount') + $smTransactions->sum('amount') + $gaTransactions->sum('amount');
        $netCfOperating = $cfOperatingInflows - $cfOperatingOutflows;

        $cfInvestingOutflows = $periodTransactions->filter(fn($t) => $t->type === 'expense' && $this->categoryContains($t, $investingKeywords))->sum('amount');
        $netCfInvesting = -$cfInvestingOutflows;

        $cfFinancingInflows = $periodTransactions->filter(fn($t) => $t->type === 'income' && $this->categoryContains($t, $financingKeywords))->sum('amount');
        $cfFinancingOutflows = $periodTransactions->filter(fn($t) => $t->type === 'expense' && $this->categoryContains($t, $financingKeywords))->sum('amount');
        $netCfFinancing = $cfFinancingInflows - $cfFinancingOutflows;

        // --- (4) RATIO ANALYSIS LOGIC ---
        $returnOnEquity = $totalEquity > 0 ? ($netProfit / $totalEquity) * 100 : 0;

        // --- Assemble Response ---
        return response()->json([
            'summary' => ['total_revenue' => (float)$totalRevenue, 'total_expenses' => (float)$totalCogs + $totalOperatingExpenses, 'net_profit' => (float)$netProfit, 'profit_margin' => (float)$profitMargin],
            'income_statement' => [
                'revenues' => $this->groupAndFormat($revenueTransactions),
                'total_revenue' => (float)$totalRevenue,
                'cogs' => $this->groupAndFormat($cogsTransactions),
                'total_cogs' => (float)$totalCogs,
                'gross_profit' => (float)$grossProfit,
                'sales_marketing_expenses' => $this->groupAndFormat($smTransactions),
                'total_sm_expenses' => (float)$totalSmExpenses,
                'general_admin_expenses' => $this->groupAndFormat($gaTransactions),
                'total_ga_expenses' => (float)$totalGaExpenses,
                'total_operating_expenses' => (float)$totalOperatingExpenses,
                'net_profit' => (float)$netProfit,
            ],
            'balance_sheet' => [
                'assets' => ['keterangan' => 'Kas dan Setara Kas', 'jumlah' => (float)$totalCash],
                'total_assets' => (float)$totalCash,
                'liabilities' => [], 'total_liabilities' => 0,
                'equity' => ['keterangan' => 'Akumulasi Laba', 'jumlah' => (float)$totalEquity],
                'total_equity' => (float)$totalEquity,
            ],
            'cash_flow_statement' => [
                'operating' => ['inflows' => $cfOperatingInflows, 'outflows' => $cfOperatingOutflows, 'net' => $netCfOperating],
                'investing' => ['inflows' => 0, 'outflows' => $cfInvestingOutflows, 'net' => $netCfInvesting],
                'financing' => ['inflows' => $cfFinancingInflows, 'outflows' => $cfFinancingOutflows, 'net' => $netCfFinancing],
                'net_cash_flow' => $netCfOperating + $netCfInvesting + $netCfFinancing,
            ],
            'ratios' => [
                ['rasio' => 'Margin Laba Bersih', 'nilai' => $profitMargin, 'interpretasi' => 'Efisiensi dalam menghasilkan laba dari pendapatan.'],
                ['rasio' => 'Return on Equity (ROE)', 'nilai' => $returnOnEquity, 'interpretasi' => 'Kemampuan perusahaan menghasilkan laba dari modal sendiri.'],
            ]
        ]);
    }

    private function categoryContains($transaction, $keywords)
    {
        if (!$transaction->category) return false;
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($transaction->category->name), $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function groupAndFormat($transactions)
    {
        if ($transactions->isEmpty()) return [];
        return $transactions->groupBy('category.name')->map(fn($g) => $g->sum('amount'))->sortDesc()->map(fn($a, $c) => ['keterangan' => $c, 'jumlah' => (float) $a])->values();
    }
}
