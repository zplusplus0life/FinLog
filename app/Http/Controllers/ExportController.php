<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function downloadPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $period = $this->getPeriodLabel($request);

        $pdf = Pdf::loadView('reports.financial_report', ['data' => $data, 'period' => $period]);
        
        return $pdf->download('laporan_keuangan_' . time() . '.pdf');
    }

    // This is the same logic from TransactionController, refactored here.
    private function getReportData(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $periodQuery = FinancialTransaction::with('category')->where('status', 'approved')->whereYear('transaction_date', $validated['year']);
        if (!empty($validated['month'])) {
            $periodQuery->whereMonth('transaction_date', $validated['month']);
        }
        $periodTransactions = $periodQuery->get();
        $allTransactions = FinancialTransaction::where('status', 'approved')->get();

        $cogsKeywords = ['hpp', 'bahan baku', 'cost of sales', 'inventory'];
        $smKeywords = ['pemasaran', 'iklan', 'promosi', 'sales', 'marketing'];
        $investingKeywords = ['aset', 'properti', 'peralatan', 'investasi'];
        $financingKeywords = ['pinjaman', 'modal', 'saham', 'dividen'];

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

        $totalCash = $allTransactions->where('type', 'income')->sum('amount') - $allTransactions->where('type', 'expense')->sum('amount');
        $totalEquity = $totalCash;

        $cfOperatingInflows = $revenueTransactions->sum('amount');
        $cfOperatingOutflows = $cogsTransactions->sum('amount') + $smTransactions->sum('amount') + $gaTransactions->sum('amount');
        $netCfOperating = $cfOperatingInflows - $cfOperatingOutflows;

        $cfInvestingOutflows = $periodTransactions->filter(fn($t) => $t->type === 'expense' && $this->categoryContains($t, $investingKeywords))->sum('amount');
        $netCfInvesting = -$cfInvestingOutflows;

        $cfFinancingInflows = $periodTransactions->filter(fn($t) => $t->type === 'income' && $this->categoryContains($t, $financingKeywords))->sum('amount');
        $cfFinancingOutflows = $periodTransactions->filter(fn($t) => $t->type === 'expense' && $this->categoryContains($t, $financingKeywords))->sum('amount');
        $netCfFinancing = $cfFinancingInflows - $cfFinancingOutflows;

        $returnOnEquity = $totalEquity > 0 ? ($netProfit / $totalEquity) * 100 : 0;

        return [
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
                'operating' => ['inflows' => (float)$cfOperatingInflows, 'outflows' => (float)$cfOperatingOutflows, 'net' => (float)$netCfOperating],
                'investing' => ['inflows' => 0, 'outflows' => (float)$cfInvestingOutflows, 'net' => (float)$netCfInvesting],
                'financing' => ['inflows' => (float)$cfFinancingInflows, 'outflows' => (float)$cfFinancingOutflows, 'net' => (float)$netCfFinancing],
                'net_cash_flow' => (float)($netCfOperating + $netCfInvesting + $netCfFinancing),
            ],
            'ratios' => [
                ['rasio' => 'Margin Laba Bersih', 'nilai' => (float)$profitMargin, 'interpretasi' => 'Mengukur efisiensi perusahaan dalam menghasilkan laba dari pendapatan.'],
                ['rasio' => 'Return on Equity (ROE)', 'nilai' => (float)$returnOnEquity, 'interpretasi' => 'Mengukur kemampuan perusahaan menghasilkan laba dari ekuitas yang diinvestasikan.'],
            ],
            'summary' => [
                'total_revenue' => (float)$totalRevenue,
                'total_expenses' => (float)($totalCogs + $totalOperatingExpenses),
                'net_profit' => (float)$netProfit,
                'profit_margin' => (float)$profitMargin,
            ],
        ];
    }

    private function getPeriodLabel(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        if ($month) {
            return Carbon::createFromDate($year, $month, 1)->isoFormat('MMMM YYYY');
        }
        return 'Tahun ' . $year;
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