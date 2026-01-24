<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

// --- Reactive State ---
const activeItem = ref(2); // Laporan is index 2
const menuItems = ref([
    { name: 'Dashboard', icon: '📊' },
    { name: 'Verifikasi Transaksi', icon: '⚙️' },
    { name: 'Laporan', icon: '📈' }
]);

// --- NAVIGATION ---
function navigateToPage(index: number) {
    activeItem.value = index;
    const routes = [
        '/manajer/overview',
        '/manajer/verification', 
        '/manajer/laporan'
    ];
    window.location.href = routes[index];
}
const selectedPeriod = ref('monthly');
const selectedYear = ref(new Date().getFullYear().toString());
const selectedMonth = ref((new Date().getMonth() + 1).toString());
const activeTab = ref('income-statement');
const tabs = ref([
    { value: 'income-statement', label: 'Laba Rugi' },
    { value: 'balance-sheet', label: 'Neraca' },
    { value: 'cash-flow', label: 'Arus Kas' },
    { value: 'ratios', label: 'Analisis Rasio' }
]);

const reportData = ref<any>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// --- Data Fetching ---
async function fetchReportData() {
    isLoading.value = true;
    error.value = null;
    reportData.value = null;

    try {
        const params = new URLSearchParams({
            year: selectedYear.value,
            month: selectedPeriod.value === 'monthly' ? selectedMonth.value : ''
        });
        
        const response = await fetch(`/api/financial-report?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        reportData.value = await response.json();
    } catch (e: any) {
        console.error('Fetch error:', e);
        error.value = e.message || 'Gagal memuat data laporan';
    } finally {
        isLoading.value = false;
    }
}

// --- Watchers and Lifecycle ---
onMounted(fetchReportData);
watch([selectedPeriod, selectedYear, selectedMonth], fetchReportData, { immediate: false });


// --- Helper & Export Functions ---
function formatCurrency(amount: number) {
    if (typeof amount !== 'number') return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

function exportPDF() {
    const params = new URLSearchParams({
        year: selectedYear.value,
        month: selectedPeriod.value === 'monthly' ? selectedMonth.value : ''
    });
    window.location.href = `/api/export/pdf?${params.toString()}`;
}

function exportExcel() {
    if (!reportData.value) return;

    const periodLabel = selectedPeriod.value === 'monthly' 
        ? `${selectedMonth.value}-${selectedYear.value}` 
        : selectedYear.value;
    const title = `Laporan_Keuangan_Lengkap_${periodLabel}`;
    let csvRows = [];

    // --- Laba Rugi ---
    csvRows.push(['Laporan Laba Rugi', '', '']);
    csvRows.push(['Kelompok', 'Keterangan', 'Jumlah']);
    
    const is = reportData.value.income_statement;
    csvRows.push(['Pendapatan', '', '']);
    is.revenues.forEach((item: any) => csvRows.push(['', item.keterangan, item.jumlah]));
    csvRows.push(['Total Pendapatan', '', is.total_revenue]);
    csvRows.push([]);

    csvRows.push(['Harga Pokok Penjualan', '', '']);
    is.cogs.forEach((item: any) => csvRows.push(['', item.keterangan, -item.jumlah]));
    csvRows.push(['Total HPP', '', -is.total_cogs]);
    csvRows.push(['Laba Kotor', '', is.gross_profit]);
    csvRows.push([]);

    csvRows.push(['Beban Operasional', '', '']);
    if (is.sales_marketing_expenses.length) {
        is.sales_marketing_expenses.forEach((item: any) => csvRows.push(['Beban Penjualan & Pemasaran', item.keterangan, -item.jumlah]));
    }
    if (is.general_admin_expenses.length) {
        is.general_admin_expenses.forEach((item: any) => csvRows.push(['Beban Umum & Administrasi', item.keterangan, -item.jumlah]));
    }
    csvRows.push(['Total Beban Operasional', '', -is.total_operating_expenses]);
    csvRows.push([]);

    csvRows.push(['Laba Bersih (sebelum pajak)', '', is.net_profit]);
    csvRows.push([]);
    csvRows.push([]);

    // --- Neraca ---
    csvRows.push(['Neraca (Sederhana)', '', '', '']);
    csvRows.push(['Aktiva', 'Jumlah (Rp)', 'Passiva', 'Jumlah (Rp)']);
    const bs = reportData.value.balance_sheet;
    csvRows.push(['Aset Lancar', '', 'Liabilitas', '']);
    csvRows.push([bs.assets.keterangan, bs.assets.jumlah, 'Tidak ada liabilitas', 0]);
    csvRows.push(['Total Aset', bs.total_assets, 'Total Liabilitas', bs.total_liabilities]);
    csvRows.push(['', '', 'Ekuitas', '']);
    csvRows.push(['', '', bs.equity.keterangan, bs.equity.jumlah]);
    csvRows.push(['', '', 'Total Ekuitas', bs.total_equity]);
    csvRows.push(['Total Aset', bs.total_assets, 'Total Liabilitas & Ekuitas', bs.total_liabilities + bs.total_equity]);
    csvRows.push([]);
    csvRows.push([]);

    // --- Arus Kas ---
    csvRows.push(['Laporan Arus Kas', '', '']);
    csvRows.push(['Kelompok', 'Keterangan', 'Jumlah']);
    const cf = reportData.value.cash_flow_statement;
    csvRows.push(['Arus Kas dari Aktivitas Operasi', '', '']);
    csvRows.push(['', 'Arus Kas Masuk dari Pelanggan', cf.operating.inflows]);
    csvRows.push(['', 'Pembayaran kepada Pemasok dan Karyawan', -cf.operating.outflows]);
    csvRows.push(['Arus Kas Bersih dari Aktivitas Operasi', '', cf.operating.net]);
    csvRows.push([]);

    csvRows.push(['Arus Kas dari Aktivitas Investasi', '', '']);
    csvRows.push(['', 'Pembelian Aset Tetap', -cf.investing.outflows]);
    csvRows.push(['Arus Kas Bersih dari Aktivitas Investasi', '', cf.investing.net]);
    csvRows.push([]);

    csvRows.push(['Arus Kas dari Aktivitas Pendanaan', '', '']);
    csvRows.push(['', 'Penerimaan dari Pinjaman/Modal', cf.financing.inflows]);
    csvRows.push(['', 'Pembayaran Pinjaman/Dividen', -cf.financing.outflows]);
    csvRows.push(['Arus Kas Bersih dari Aktivitas Pendanaan', '', cf.financing.net]);
    csvRows.push([]);

    csvRows.push(['Kenaikan (Penurunan) Bersih Kas', '', cf.net_cash_flow]);
    csvRows.push([]);
    csvRows.push([]);

    // --- Analisis Rasio ---
    csvRows.push(['Analisis Rasio Keuangan', '', '', '']);
    csvRows.push(['Rasio Keuangan', 'Nilai', 'Interpretasi']);
    reportData.value.ratios.forEach((item: any) => csvRows.push([item.rasio, `${item.nilai.toFixed(2)} %`, item.interpretasi]));

    let csvContent = "data:text/csv;charset=utf-8," + csvRows.map(e => e.join(",")).join("\n");

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `${title}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

</script>

<template>
    <div class="flex min-h-screen root-container">
        <!-- Sidebar -->
        <div class="w-60 bg-white shadow-lg fixed h-full sidebar">
            <div class="p-5">
                <h1 class="text-xl font-bold text-blue-600 mb-6">FinLog</h1>
                <ul>
                    <li v-for="(item, index) in menuItems" :key="index" 
                        :class="['px-4 py-3 rounded-lg mb-1 flex items-center gap-3 cursor-pointer transition-all',
                                 activeItem === index ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-50']"
                        @click="navigateToPage(index)">
                        <span class="text-lg">{{ item.icon }}</span>
                        <span class="text-sm font-medium">{{ item.name }}</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 ml-60 p-6 main-content">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200 page-header">
                <h2 class="text-2xl font-bold text-gray-800">Laporan Keuangan</h2>
                <div class="flex items-center gap-4 filter-and-export-group">
                    <div class="filter-section">
                        <div class="filter-group">
                            <label class="filter-label">Periode</label>
                            <select v-model="selectedPeriod" class="filter-select">
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Tahun</label>
                            <select v-model="selectedYear" class="filter-select">
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                        <div class="filter-group" v-if="selectedPeriod === 'monthly'">
                            <label class="filter-label">Bulan</label>
                            <select v-model="selectedMonth" class="filter-select">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                    </div>
                    <div class="export-buttons">
                        <button @click="exportPDF" class="export-button pdf-button"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>PDF</button>
                        <button @click="exportExcel" class="export-button excel-button"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Excel</button>
                        </div>
                </div>
            </div>

            <!-- Loading and Error States -->
            <div v-if="isLoading" class="text-center py-20 text-gray-500">Memuat data...</div>
            <div v-if="error" class="bg-red-100 text-red-700 p-4 rounded-lg"><b>Error:</b> {{ error }}</div>
            
            <!-- Content -->
            <template v-if="reportData && !isLoading && !error">
                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-title">Pendapatan Total</div>
                        <div class="summary-value summary-profit">{{ formatCurrency(reportData.summary.total_revenue) }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-title">Beban Operasional</div>
                        <div class="summary-value summary-loss">{{ formatCurrency(reportData.summary.total_expenses) }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-title">Laba Bersih</div>
                        <div class="summary-value" :class="reportData.summary.net_profit >= 0 ? 'summary-profit' : 'loss-loss'">{{ formatCurrency(reportData.summary.net_profit) }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-title">Margin Laba</div>
                        <div class="summary-value summary-neutral">{{ reportData.summary.profit_margin.toFixed(2) }}%</div>
                    </div>
                </div>
                
                <!-- Tabs -->
                <div class="tabs">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.value"
                        @click="activeTab = tab.value"
                        :class="['tab', activeTab === tab.value ? 'active' : '']">
                        {{ tab.label }}
                    </button>
                </div>
                
                <!-- Laba Rugi Report -->
                <div v-if="activeTab === 'income-statement'" class="report-card">
                    <div class="report-header">
                        <h3 class="report-title">Laporan Laba Rugi</h3>
                    </div>
                    <table class="financial-table">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th class="currency">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Pendapatan -->
                            <tr class="section-header"><td colspan="2">Pendapatan</td></tr>
                            <tr v-if="!reportData.income_statement.revenues.length"><td colspan="2" class="text-center py-4 text-gray-500 italic">Tidak ada pendapatan.</td></tr>
                            <tr v-for="item in reportData.income_statement.revenues" :key="'rev-'+item.keterangan">
                                <td class="pl-8">{{ item.keterangan }}</td>
                                <td class="currency">{{ formatCurrency(item.jumlah) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>Total Pendapatan</td>
                                <td class="currency">{{ formatCurrency(reportData.income_statement.total_revenue) }}</td>
                            </tr>

                            <!-- Harga Pokok Penjualan (HPP) -->
                            <tr class="section-header"><td colspan="2">Harga Pokok Penjualan</td></tr>
                            <tr v-if="!reportData.income_statement.cogs.length"><td colspan="2" class="text-center py-4 text-gray-500 italic">Tidak ada HPP.</td></tr>
                            <tr v-for="item in reportData.income_statement.cogs" :key="'cogs-'+item.keterangan">
                                <td class="pl-8">{{ item.keterangan }}</td>
                                <td class="currency">({{ formatCurrency(item.jumlah) }})</td>
                            </tr>
                            <tr class="total-row">
                                <td>Total Harga Pokok Penjualan</td>
                                <td class="currency">({{ formatCurrency(reportData.income_statement.total_cogs) }})</td>
                            </tr>

                            <!-- Laba Kotor -->
                            <tr class="total-row font-bold">
                                <td>Laba Kotor</td>
                                <td class="currency">{{ formatCurrency(reportData.income_statement.gross_profit) }}</td>
                            </tr>

                            <!-- Beban Operasional -->
                            <tr class="section-header"><td colspan="2">Beban Operasional</td></tr>
                            
                            <tr v-if="reportData.income_statement.sales_marketing_expenses.length > 0">
                                <td class="pl-8 font-semibold italic">Beban Penjualan & Pemasaran</td>
                                <td class="currency"></td>
                            </tr>
                            <tr v-for="item in reportData.income_statement.sales_marketing_expenses" :key="'sm-'+item.keterangan">
                                <td class="pl-16">{{ item.keterangan }}</td>
                                <td class="currency">({{ formatCurrency(item.jumlah) }})</td>
                            </tr>

                            <tr v-if="reportData.income_statement.general_admin_expenses.length > 0">
                                <td class="pl-8 font-semibold italic">Beban Umum & Administrasi</td>
                                <td class="currency"></td>
                            </tr>
                            <tr v-for="item in reportData.income_statement.general_admin_expenses" :key="'ga-'+item.keterangan">
                                <td class="pl-16">{{ item.keterangan }}</td>
                                <td class="currency">({{ formatCurrency(item.jumlah) }})</td>
                            </tr>
                            <tr v-if="!reportData.income_statement.sales_marketing_expenses.length && !reportData.income_statement.general_admin_expenses.length">
                                <td colspan="2" class="text-center py-4 text-gray-500 italic">Tidak ada beban operasional.</td>
                            </tr>
                            <tr class="total-row">
                                <td>Total Beban Operasional</td>
                                <td class="currency">({{ formatCurrency(reportData.income_statement.total_operating_expenses) }})</td>
                            </tr>

                            <!-- Laba Bersih -->
                            <tr class="total-row font-bold text-lg" :class="reportData.income_statement.net_profit >= 0 ? 'profit-row' : 'loss-row'">
                                <td>Laba Bersih (sebelum pajak)</td>
                                <td class="currency">{{ formatCurrency(reportData.income_statement.net_profit) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Neraca Report -->
                <div v-if="activeTab === 'balance-sheet'" class="report-card">
                    <div class="report-header"><h3 class="report-title">Neraca (Sederhana)</h3></div>
                    <table class="financial-table">
                        <thead>
                            <tr>
                                <th>Aktiva</th>
                                <th class="currency">Jumlah (Rp)</th>
                                <th>Passiva</th>
                                <th class="currency">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-semibold">Aset Lancar</td>
                                <td></td>
                                <td class="font-semibold">Liabilitas</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="pl-8">{{ reportData.balance_sheet.assets.keterangan }}</td>
                                <td class="currency">{{ formatCurrency(reportData.balance_sheet.assets.jumlah) }}</td>
                                <td class="pl-8 italic">Tidak ada liabilitas</td>
                                <td class="currency">{{ formatCurrency(0) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>Total Aset</td>
                                <td class="currency">{{ formatCurrency(reportData.balance_sheet.total_assets) }}</td>
                                <td>Total Liabilitas</td>
                                <td class="currency">{{ formatCurrency(reportData.balance_sheet.total_liabilities) }}</td>
                            </tr>
                            <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
                             <tr>
                                <td class="font-semibold"></td>
                                <td></td>
                                <td class="font-semibold">Ekuitas</td>
                                <td></td>
                            </tr>
                             <tr>
                                <td></td>
                                <td></td>
                                <td class="pl-8">{{ reportData.balance_sheet.equity.keterangan }}</td>
                                <td class="currency">{{ formatCurrency(reportData.balance_sheet.equity.jumlah) }}</td>
                            </tr>
                             <tr class="total-row">
                                <td></td>
                                <td></td>
                                <td>Total Ekuitas</td>
                                <td class="currency">{{ formatCurrency(reportData.balance_sheet.total_equity) }}</td>
                            </tr>
                             <tr class="total-row font-bold">
                                <td>Total Aset</td>
                                <td class="currency">{{ formatCurrency(reportData.balance_sheet.total_assets) }}</td>
                                <td>Total Liabilitas & Ekuitas</td>
                                <td class="currency">{{ formatCurrency(reportData.balance_sheet.total_liabilities + reportData.balance_sheet.total_equity) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-xs text-gray-500 mt-4 italic">*Neraca disederhanakan, mengasumsikan semua aset adalah kas dan tidak ada utang.</p>
                </div>

                <!-- Arus Kas Report -->
                <div v-if="activeTab === 'cash-flow'" class="report-card">
                    <div class="report-header"><h3 class="report-title">Laporan Arus Kas</h3></div>
                     <table class="financial-table">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th class="currency">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="section-header"><td colspan="2">Arus Kas dari Aktivitas Operasi</td></tr>
                            <tr>
                                <td class="pl-8">Arus Kas Masuk dari Pelanggan</td>
                                <td class="currency">{{ formatCurrency(reportData.cash_flow_statement.operating.inflows) }}</td>
                            </tr>
                             <tr>
                                <td class="pl-8">Pembayaran kepada Pemasok dan Karyawan</td>
                                <td class="currency">({{ formatCurrency(reportData.cash_flow_statement.operating.outflows) }})</td>
                            </tr>
                            <tr class="total-row">
                                <td>Arus Kas Bersih dari Aktivitas Operasi</td>
                                <td class="currency">{{ formatCurrency(reportData.cash_flow_statement.operating.net) }}</td>
                            </tr>

                            <tr class="section-header"><td colspan="2">Arus Kas dari Aktivitas Investasi</td></tr>
                            <tr>
                                <td class="pl-8">Pembelian Aset Tetap</td>
                                <td class="currency">({{ formatCurrency(reportData.cash_flow_statement.investing.outflows) }})</td>
                            </tr>
                            <tr class="total-row">
                                <td>Arus Kas Bersih dari Aktivitas Investasi</td>
                                <td class="currency">{{ formatCurrency(reportData.cash_flow_statement.investing.net) }}</td>
                            </tr>

                            <tr class="section-header"><td colspan="2">Arus Kas dari Aktivitas Pendanaan</td></tr>
                             <tr>
                                <td class="pl-8">Penerimaan dari Pinjaman/Modal</td>
                                <td class="currency">{{ formatCurrency(reportData.cash_flow_statement.financing.inflows) }}</td>
                            </tr>
                             <tr>
                                <td class="pl-8">Pembayaran Pinjaman/Dividen</td>
                                <td class="currency">({{ formatCurrency(reportData.cash_flow_statement.financing.outflows) }})</td>
                            </tr>
                            <tr class="total-row">
                                <td>Arus Kas Bersih dari Aktivitas Pendanaan</td>
                                <td class="currency">{{ formatCurrency(reportData.cash_flow_statement.financing.net) }}</td>
                            </tr>

                            <tr class="total-row font-bold" :class="reportData.cash_flow_statement.net_cash_flow >= 0 ? 'profit-row' : 'loss-row'">
                                <td>Kenaikan (Penurunan) Bersih Kas</td>
                                <td class="currency">{{ formatCurrency(reportData.cash_flow_statement.net_cash_flow) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-xs text-gray-500 mt-4 italic">*Arus kas dihitung berdasarkan asumsi dari nama kategori transaksi.</p>
                </div>

                <!-- Analisis Rasio Report -->
                <div v-if="activeTab === 'ratios'" class="report-card">
                     <div class="report-header"><h3 class="report-title">Analisis Rasio Keuangan</h3></div>
                     <table class="financial-table">
                        <thead>
                            <tr>
                                <th>Rasio Keuangan</th>
                                <th class="currency">Nilai</th>
                                <th>Interpretasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!reportData.ratios.length"><td colspan="3" class="text-center py-12 text-gray-500">Data belum tersedia.</td></tr>
                            <tr v-for="item in reportData.ratios" :key="item.rasio">
                                <td class="font-medium">{{ item.rasio }}</td>
                                <td class="currency font-semibold">{{ item.nilai.toFixed(2) }} %</td>
                                <td class="text-sm text-gray-600">{{ item.interpretasi }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

@media print {
    .sidebar, .page-header, .tabs, .export-buttons, .summary-cards, .filter-section {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .root-container {
        display: block !important;
    }

    .report-card {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
        page-break-inside: avoid !important;
    }
}

body {
    font-family: 'Inter', sans-serif;
}

.report-card {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.report-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.export-buttons {
    display: flex;
    gap: 12px;
}

.export-button {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.pdf-button {
    background-color: #ef4444;
    color: white;
}

.excel-button {
    background-color: #10b981;
    color: white;
}

.export-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.financial-table {
    width: 100%;
    border-collapse: collapse;
}

.financial-table th,
.financial-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.financial-table th {
    font-weight: 600;
    color: #374151;
    background-color: #f9fafb;
}

.financial-table tr:last-child td {
    border-bottom: none;
}

.total-row {
    background-color: #f3f4f6;
    font-weight: 600;
}

.profit-row {
    color: #16a34a;
}

.loss-row {
    color: #dc2626;
}

.section-header {
    background-color: #f9fafb;
    font-weight: 600;
}

.currency {
    text-align: right;
    font-family: 'Courier New', monospace;
}

.filter-section {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-label {
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background-color: white;
    min-width: 160px;
}

.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.summary-title {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
}

.summary-value {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 4px;
}

.summary-profit {
    color: #16a34a;
}

.summary-loss {
    color: #dc2626;
}

.summary-neutral {
    color: #374151;
}

.summary-change {
    font-size: 12px;
    font-weight: 500;
}

.change-positive {
    color: #16a34a;
}

.change-negative {
    color: #dc2626;
}

.tabs {
    display: flex;
    gap: 1px;
    background-color: #e5e7eb;
    border-radius: 8px;
    margin-bottom: 24px;
    width: fit-content;
}

.tab {
    padding: 12px 24px;
    background-color: white;
    border: none;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 8px;
}

.tab:hover {
    background-color: #f3f4f6;
}

.tab.active {
    background-color: #3b82f6;
    color: white;
}

.chart-placeholder {
    height: 300px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    margin: 20px 0;
}
</style>