
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

// --- PROPS ---
const props = defineProps({
    transactions: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
});

// --- HELPERS ---
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
};

const getMonthName = (monthNumber: number) => {
    const date = new Date();
    date.setMonth(monthNumber - 1);
    return date.toLocaleString('en-US', { month: 'short' });
};

// --- REACTIVE STATE ---
const activeItem = ref(0);
const selectedPeriod = ref('Year-to-date');
const showPeriodDropdown = ref(false);
const showUserDropdown = ref(false);
const menuItems = ref([
    { name: 'Dashboard', icon: '📊' },
    { name: 'Laporan', icon: '📈' }
]);
const growthTooltip = ref<HTMLElement | null>(null);
const cashFlowTooltip = ref<HTMLElement | null>(null);
const revenueTooltip = ref<HTMLElement | null>(null);
const profitTooltip = ref<HTMLElement | null>(null);

// --- DROPDOWN LOGIC ---
function togglePeriodDropdown() {
    showPeriodDropdown.value = !showPeriodDropdown.value;
}

function toggleUserDropdown() {
    showUserDropdown.value = !showUserDropdown.value;
}

// --- NAVIGATION ---
function navigateToPage(index: number) {
    activeItem.value = index;
    const routes = [
        '/viewer/overview',
        '/viewer/laporan'
    ];
    window.location.href = routes[index];
}

function closePeriodDropdown(event: Event) {
    const target = event.target as Element;
    if (growthTooltip.value && target && !growthTooltip.value.contains(target)) {
        showPeriodDropdown.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closePeriodDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closePeriodDropdown);
});

// --- PERIOD FILTERING LOGIC ---
const getPeriodDateRange = (period: string) => {
    const now = new Date();
    let startDate = new Date();
    let endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);

    switch (period) {
        case 'Year-to-date':
            startDate = new Date(2025, 0, 1, 0, 0, 0, 0); // Force 2025 since our data is in 2025
            break;
        case 'Month-to-month':
            startDate = new Date(2025, now.getMonth(), 1, 0, 0, 0, 0);
            break;
        case 'Quarter-to-quarter':
            startDate = new Date(2025, now.getMonth() - (now.getMonth() % 3), 1, 0, 0, 0, 0);
            break;
        case 'Last 30 days':
            startDate.setDate(now.getDate() - 30);
            startDate.setHours(0, 0, 0, 0);
            break;
        case 'Last 7 days':
            startDate.setDate(now.getDate() - 7);
            startDate.setHours(0, 0, 0, 0);
            break;
        default:
            startDate = new Date(2025, 0, 1, 0, 0, 0, 0);
            break;
    }
    return { startDate, endDate };
};

const filteredTransactionsByPeriod = computed(() => {
    const { startDate, endDate } = getPeriodDateRange(selectedPeriod.value);

    // Filter only approved transactions for overview display
    return props.transactions
        .filter((t: any) => t.status === 'approved')
        .filter((t: any) => {
            const transactionDate = new Date(t.transaction_date);
            transactionDate.setHours(0, 0, 0, 0); // Normalize transactionDate to beginning of day for comparison

            return transactionDate >= startDate && transactionDate <= endDate;
        });
});

// --- BASE CALCULATIONS (using filteredTransactionsByPeriod) ---

const periodDataPoints = computed(() => {
    const { startDate, endDate } = getPeriodDateRange(selectedPeriod.value);
    const points = [];

    if (selectedPeriod.value.includes('day')) { // Last 7 days, Last 30 days
        let current = new Date(startDate);
        while (current <= endDate) {
            points.push({
                label: current.toLocaleDateString('en-US', { day: 'numeric', month: 'short' }),
                date: new Date(current),
                income: 0,
                expense: 0,
            });
            current.setDate(current.getDate() + 1);
        }
    } else { // Month-based periods
        let current = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        while (current <= endDate) {
            points.push({
                label: current.toLocaleString('en-US', { month: 'short' }),
                date: new Date(current),
                income: 0,
                expense: 0,
            });
            current.setMonth(current.getMonth() + 1);
        }
    }
    return points;
});

const calculatedPeriodData = computed(() => {
    const dataPoints = periodDataPoints.value.map(point => ({
        ...point,
        date: new Date(point.date),
        income: point.income,
        expense: point.expense
    }));

    filteredTransactionsByPeriod.value.forEach((t: any) => {
        const transactionDate = new Date(t.transaction_date);
        const amount = parseFloat(t.amount);

        const targetPoint = dataPoints.find((dp: any) => {
            if (selectedPeriod.value.includes('day')) {
                return dp.date.toDateString() === transactionDate.toDateString();
            } else {
                return dp.date.getFullYear() === transactionDate.getFullYear() &&
                       dp.date.getMonth() === transactionDate.getMonth();
            }
        });

        if (targetPoint) {
            if (t.type === 'income') {
                targetPoint.income += amount;
            } else {
                targetPoint.expense += amount;
            }
        }
    });

    return dataPoints.map((dp: any) => ({
        ...dp,
        net: dp.income - dp.expense,
        profitMargin: dp.income > 0 ? ((dp.income - dp.expense) / dp.income) * 100 : 0,
    }));
});

const totals = computed(() => {
    const totalRevenue = calculatedPeriodData.value.reduce((acc, dp) => acc + dp.income, 0);
    const totalExpenses = calculatedPeriodData.value.reduce((acc, dp) => acc + dp.expense, 0);
    const netProfit = totalRevenue - totalExpenses;
    const profitMargin = totalRevenue > 0 ? (netProfit / totalRevenue) * 100 : 0;

    return { totalRevenue, totalExpenses, netProfit, profitMargin };
});

const expenseByCategory = computed(() => {
    const expenses = filteredTransactionsByPeriod.value.filter((t: any) => t.type === 'expense');
    const byCategory: Record<string, number> = {};

    expenses.forEach((t: any) => {
        const categoryName = t.category?.name || 'Uncategorized';
        if (!byCategory[categoryName]) {
            byCategory[categoryName] = 0;
        }
        byCategory[categoryName] += parseFloat(t.amount);
    });

    const total = Object.values(byCategory).reduce((acc: number, v: number) => acc + v, 0);

    return Object.entries(byCategory).map(([name, amount]) => ({
        name,
        amount,
        percentage: total > 0 ? (amount / total) * 100 : 0,
    })).sort((a: any, b: any) => b.amount - a.amount);
});


// --- DATA FOR CHARTS & CARDS ---

const growthData = computed(() => {
    const allValues = calculatedPeriodData.value.map(m => m.net);
    const max = Math.max(0, ...allValues.map(Math.abs));
    return allValues.map(v => {
        const percentage = max > 0 ? (v / max) * 100 : 0;
        return isNaN(percentage) ? 0 : Math.max(0, percentage);
    });
});

const netProfitCard = computed(() => {
    const vat = totals.value.totalRevenue * 0.11; // 11% VAT
    return {
        netProfit: totals.value.netProfit,
        operatingCosts: totals.value.totalExpenses,
        profitMargin: totals.value.profitMargin,
        vat: vat,
    };
});

const cashFlowData = computed(() => {
    const incomeValues = calculatedPeriodData.value.map((m: any) => m.income);
    const expenseValues = calculatedPeriodData.value.map((m: any) => m.expense);
    const max = Math.max(0, ...incomeValues, ...expenseValues);

    const interleaved: any[] = [];
    calculatedPeriodData.value.forEach((dp: any, i: number) => {
        const incomePercentage = max > 0 ? (incomeValues[i] / max) * 100 : 0;
        const expensePercentage = max > 0 ? (expenseValues[i] / max) * 100 : 0;
        
        interleaved.push({ 
            type: 'income', 
            value: isNaN(incomePercentage) ? 0 : Math.max(0, incomePercentage)
        });
        interleaved.push({ 
            type: 'outflow', 
            value: isNaN(expensePercentage) ? 0 : Math.max(0, expensePercentage)
        });
    });
    return interleaved;
});

const expenseChart = computed(() => {
    const top4 = expenseByCategory.value.slice(0, 4);
    let cumulative = 0;
    const colors = ['#ec4899', '#f59e0b', '#10b981', '#8b5cf6'];
    
    const segments = top4.map((cat, i) => {
        const start = cumulative;
        cumulative += cat.percentage;
        return { ...cat, start, end: cumulative, color: colors[i] };
    });

    const conicGradient = segments.length > 0 
        ? segments.map(s => `${s.color} ${s.start}% ${s.end}%`).join(', ') + `, #e5e7eb ${cumulative}% 100%`
        : '#e5e7eb 0% 100%';

    return {
        segments,
        gradient: `conic-gradient(${conicGradient})`,
    };
});

const revenueData = computed(() => {
    const allValues = calculatedPeriodData.value.map(m => m.income);
    const max = Math.max(0, ...allValues);
    return allValues.map(v => {
        const percentage = max > 0 ? (v / max) * 100 : 0;
        return isNaN(percentage) ? 0 : Math.max(0, percentage);
    });
});

const revenueTable = computed(() => {
    return calculatedPeriodData.value.map((dp, i) => {
        const prevPoint = i > 0 ? calculatedPeriodData.value[i - 1] : { income: 0 };
        const growth = prevPoint.income > 0 ? ((dp.income - prevPoint.income) / prevPoint.income) * 100 : (dp.income > 0 ? 100 : 0);
        return {
            month: dp.label,
            revenue: dp.income,
            growth: growth,
        }
    });
});

const profitMarginData = computed(() => {
    return calculatedPeriodData.value.map((m: any) => {
        const margin = m.profitMargin;
        return isNaN(margin) ? 0 : Math.max(0, Math.min(100, margin));
    });
});

const profitMarginTable = computed(() => {
    return calculatedPeriodData.value.map((m: any) => ({
        month: m.label,
        margin: m.profitMargin,
        target: 25, // Hardcoded target
        variance: m.profitMargin - 25,
    }));
});

// --- TOOLTIP LOGIC ---
function showTooltip(event: MouseEvent, text: string) {
    const target = event.currentTarget as HTMLElement;
    if (target) {
        const tooltip = target.closest('.relative')?.querySelector('.tooltip') as HTMLElement;
        if (tooltip) {
            tooltip.textContent = text;
            const rect = target.getBoundingClientRect();
            tooltip.style.left = (event.clientX - rect.left + 10) + 'px';
            tooltip.style.top = (event.clientY - rect.top - 40) + 'px';
            tooltip.classList.add('visible');
        }
    }
}

function hideTooltip(event: MouseEvent) {
    const target = event.currentTarget as HTMLElement;
    if (target) {
        const tooltip = target.closest('.relative')?.querySelector('.tooltip') as HTMLElement;
        if (tooltip) {
            tooltip.classList.remove('visible');
        }
    }
}

</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="w-60 bg-white shadow-lg fixed h-full">
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
        <div class="flex-1 ml-60 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Overview</h2>
                <div class="flex items-center gap-4">
                    <div class="flex items-center bg-white rounded-full px-4 py-2 shadow-sm border border-gray-200">
                        <input type="text" placeholder="Search for something..." class="outline-none text-sm w-64">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="relative">
                        <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg shadow-sm cursor-pointer" @click="toggleUserDropdown">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">JS</div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div v-if="showUserDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-10">
                            <div class="py-1">
                                <Link href="/logout" method="post" as="button" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- First Row -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Growth Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">Growth (Net Profit)</h3>
                        <div class="dropdown" ref="growthTooltip">
                            <div class="dropdown-button flex items-center gap-2" @click="togglePeriodDropdown">
                                <span>{{ selectedPeriod }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <div class="dropdown-content" :class="{ 'block': showPeriodDropdown }">
                                <a href="#" @click.prevent="selectedPeriod = 'Year-to-date'; showPeriodDropdown = false;" 
                                   :class="['flex items-center justify-between', selectedPeriod === 'Year-to-date' ? 'dropdown-item active' : '']">
                                    Year-to-date
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" @click.prevent="selectedPeriod = 'Month-to-month'; showPeriodDropdown = false;" 
                                   :class="['flex items-center justify-between', selectedPeriod === 'Month-to-month' ? 'dropdown-item active' : '']">
                                    Month-to-month
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" @click.prevent="selectedPeriod = 'Quarter-to-quarter'; showPeriodDropdown = false;" 
                                   :class="['flex items-center justify-between', selectedPeriod === 'Quarter-to-quarter' ? 'dropdown-item active' : '']">
                                    Quarter-to-quarter
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" @click.prevent="selectedPeriod = 'Last 30 days'; showPeriodDropdown = false;" 
                                   :class="['flex items-center justify-between', selectedPeriod === 'Last 30 days' ? 'dropdown-item active' : '']">
                                    Last 30 days
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" @click.prevent="selectedPeriod = 'Last 7 days'; showPeriodDropdown = false;" 
                                   :class="['flex items-center justify-between', selectedPeriod === 'Last 7 days' ? 'dropdown-item active' : '']">
                                    Last 7 days
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mb-6">
                        <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(totals.netProfit) }}</div>
                        <div :class="totals.netProfit >= 0 ? 'text-green-500' : 'text-red-500'" class="text-sm font-semibold">{{ totals.profitMargin.toFixed(2) }}%</div>
                    </div>
                    <div class="relative h-40 bg-gray-50 rounded-lg p-4">
                        <div class="flex h-full items-end justify-between gap-1">
                            <div v-for="(value, index) in growthData" :key="index" 
                                 class="growth-bar bg-blue-600 w-full max-w-[24px]" 
                                 :style="{ height: value + '%' }"
                                 @mouseover="showTooltip($event, `${calculatedPeriodData[index].label}: ${formatCurrency(calculatedPeriodData[index].net)}`)"
                                 @mouseleave="hideTooltip">
                            </div>
                        </div>
                        <div ref="growthTooltip" class="tooltip"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                        <span v-for="dp in calculatedPeriodData" :key="dp.label">{{ dp.label }}</span>
                    </div>
                </div>
                
                <!-- Net Profit Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Net Profit</h3>
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(netProfitCard.netProfit) }}</div>
                        <div class="text-sm text-gray-500">Total</div>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-xs text-gray-500">Operating Costs</div>
                        <div class="text-xs text-gray-500">VAT</div>
                    </div>
                    <div class="flex justify-between items-center mb-6">
                        <div class="text-xs text-gray-700">{{ formatCurrency(netProfitCard.operatingCosts) }}</div>
                        <div class="text-xs text-gray-700">{{ formatCurrency(netProfitCard.vat) }}</div>
                    </div>
                    <div class="flex justify-center mb-4">
                        <div class="w-36 h-36 rounded-full bg-gray-100 relative" 
                             :style="{ background: `conic-gradient(#3b82f6 0% ${netProfitCard.profitMargin}%, #e5e7eb ${netProfitCard.profitMargin}% 100%)` }">
                            <div class="absolute inset-2 bg-white rounded-full flex flex-col items-center justify-center">
                                <div class="text-xl font-bold text-gray-900">{{ netProfitCard.profitMargin.toFixed(1) }}%</div>
                                <div class="text-xs text-gray-500">Margin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Second Row -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Cash Flow Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Cash Flow</h3>
                    <div class="relative h-40 bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="flex h-full items-end justify-around">
                            <div v-for="(item, index) in cashFlowData" :key="index" 
                                 class="cash-flow-bar w-3" 
                                 :class="item.type === 'income' ? 'bg-green-500' : 'bg-red-500'"
                                 :style="{ height: item.value + '%' }"
                                 @mouseover="showTooltip($event, `${item.type === 'income' ? 'Income' : 'Outflow'}: ${formatCurrency(item.type === 'income' ? calculatedPeriodData[Math.floor(index/2)].income : calculatedPeriodData[Math.floor(index/2)].expense)}`)"
                                 @mouseleave="hideTooltip">
                            </div>
                        </div>
                        <div ref="cashFlowTooltip" class="tooltip"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                         <span v-for="dp in calculatedPeriodData" :key="dp.label">{{ dp.label }}</span>
                    </div>
                    <div class="flex flex-wrap gap-4 mt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-xs text-gray-600">Income</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-xs text-gray-600">Outflow</span>
                        </div>
                    </div>
                </div>
                
                <!-- Expense Statistics Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Expense Statistics</h3>
                    <div class="flex justify-center mb-6">
                        <div class="w-36 h-36 rounded-full relative" :style="{ background: expenseChart.gradient }">
                            <div class="absolute inset-3 bg-white rounded-full"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="item in expenseChart.segments" :key="item.name" class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: item.color }"></div>
                            <span class="text-xs text-gray-600">{{ item.name }} ({{ item.percentage.toFixed(1) }}%)</span>
                        </div>
                         <div v-if="!expenseChart.segments.length" class="col-span-2 text-center text-gray-500 italic">No expense data</div>
                    </div>
                </div>
            </div>
            
            <!-- Third Row -->
            <div class="grid grid-cols-2 gap-6">
                <!-- Revenue Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Revenue</h3>
                    <div class="relative h-40 bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="flex h-full items-end justify-between gap-1">
                            <div v-for="(value, index) in revenueData" :key="index" 
                                 class="revenue-bar w-full max-w-[24px]" 
                                 :style="{ height: value + '%' }"
                                 @mouseover="showTooltip($event, `${calculatedPeriodData[index].label}: ${formatCurrency(calculatedPeriodData[index].income)}`)"
                                 @mouseleave="hideTooltip">
                            </div>
                        </div>
                        <div ref="revenueTooltip" class="tooltip"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                        <span v-for="dp in calculatedPeriodData" :key="dp.label">{{ dp.label }}</span>
                    </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200">
                                <tr>
                                    <th class="text-left py-2 font-medium text-gray-600">Period</th>
                                    <th class="text-left py-2 font-medium text-gray-600">Revenue</th>
                                    <th class="text-left py-2 font-medium text-gray-600">Growth</th>
                                    <th class="text-left py-2 font-medium text-gray-600">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!revenueTable.some(r => r.revenue > 0)">
                                    <td colspan="4" class="text-center py-12 text-gray-500 italic">Data belum tersedia.</td>
                                </tr>
                                <tr v-else v-for="item in revenueTable" :key="item.month">
                                    <td class="py-2">{{ item.month }}</td>
                                    <td class="py-2">{{ formatCurrency(item.revenue) }}</td>
                                    <td class="py-2" :class="item.growth >= 0 ? 'text-green-500' : 'text-red-500'">{{ item.growth.toFixed(1) }}%</td>
                                    <td class="py-2">
                                        <span :class="item.growth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-full text-xs font-medium">
                                            {{ item.growth >= 0 ? 'Up' : 'Down' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Profit Margin Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Profit Margin (%)</h3>
                    <div class="relative h-40 bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="chart-container">
                            <div class="y-axis-labels">
                                <span>100%</span><span>80%</span><span>60%</span><span>40%</span><span>20%</span><span>0%</span>
                            </div>
                            <div class="h-full flex items-end justify-between gap-1">
                                <div v-for="(margin, index) in profitMarginData" :key="index" 
                                     class="profit-margin-bar w-full max-w-[24px]" 
                                     :style="{ height: margin + '%' }"
                                     @mouseover="showTooltip($event, `${calculatedPeriodData[index].label}: ${margin.toFixed(1)}%`)"
                                     @mouseleave="hideTooltip">
                                </div>
                            </div>
                        </div>
                        <div ref="profitTooltip" class="tooltip"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-500 ml-8">
                        <span v-for="dp in calculatedPeriodData" :key="dp.label">{{ dp.label }}</span>
                    </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200">
                                <tr>
                                    <th class="text-left py-2 font-medium text-gray-600">Period</th>
                                    <th class="text-left py-2 font-medium text-gray-600">Margin %</th>
                                    <th class="text-left py-2 font-medium text-gray-600">Target</th>
                                    <th class="text-left py-2 font-medium text-gray-600">Variance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!profitMarginTable.some(r => r.margin != 0)">
                                    <td colspan="4" class="text-center py-12 text-gray-500 italic">Data belum tersedia.</td>
                                </tr>
                                <tr v-else v-for="item in profitMarginTable" :key="item.month">
                                    <td class="py-2">{{ item.month }}</td>
                                    <td class="py-2">{{ item.margin.toFixed(1) }}%</td>
                                    <td class="py-2">{{ item.target.toFixed(1) }}%</td>
                                    <td class="py-2" :class="item.variance >= 0 ? 'text-green-500' : 'text-red-500'">{{ item.variance.toFixed(1) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Styles are unchanged as requested */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
}

.growth-bar, .profit-margin-bar, .cash-flow-bar, .revenue-bar {
    border-radius: 4px 4px 0 0;
    transition: all 0.3s ease;
}

.growth-bar:hover, .profit-margin-bar:hover, .cash-flow-bar:hover, .revenue-bar:hover {
    transform: scaleY(1.05);
    filter: brightness(1.1);
}

.profit-margin-bar {
    background: linear-gradient(180deg, #ef4444, #b91c1c);
}

.revenue-bar {
    background: linear-gradient(180deg, #10b981, #059669);
}

.chart-container {
    position: relative;
    height: 100%;
    padding-left: 30px;
}

.y-axis-labels {
    position: absolute;
    left: -30px;
    top: 4px;
    height: calc(100% - 8px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    font-size: 10px;
    color: #666;
    align-items: flex-end;
}

.tooltip {
    position: absolute;
    background-color: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 12px;
    pointer-events: none;
    z-index: 100;
    opacity: 0;
    transition: opacity 0.2s ease;
    white-space: nowrap;
}

.tooltip.visible {
    opacity: 1;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 180px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-radius: 8px;
    z-index: 1;
    top: 100%;
    right: 0;
    margin-top: 8px;
    padding: 8px 0;
}

.dropdown-content a {
    color: #333;
    padding: 8px 16px;
    text-decoration: none;
    display: block;
    border-radius: 4px;
    transition: background-color 0.2s;
    font-size: 13px;
}

.dropdown-content a:hover {
    background-color: #f3f4f6;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown-button {
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 6px;
    transition: background-color 0.2s;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
}

.dropdown-button:hover {
    background-color: #f3f4f6;
}

.dropdown-item.active {
    background-color: #e3f2fd;
    color: #3b82f6;
    font-weight: 500;
}

.dropdown-divider {
    height: 1px;
    background-color: #e5e7eb;
    margin: 4px 0;
}
</style>
