<?php
use App\Http\Middleware\EnforceRolePrefix;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Http\Controllers\TransactionController;

// Financial report API endpoint (for authenticated users)
Route::middleware('auth')->get('/api/financial-report', [TransactionController::class, 'getFinancialReport'])->name('reports.financial');

// Public financial report endpoint (for viewers)
Route::get('/api/public/financial-report', [TransactionController::class, 'getFinancialReport'])->name('reports.financial.public');

// Manajer transaction verification endpoints
Route::middleware(['auth', 'role:manajer'])->group(function () {
    Route::get('/api/transactions/pending', [TransactionController::class, 'pending'])->name('transactions.pending');
    Route::post('/api/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('/api/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');
});

// Staff transaction management endpoints
Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::post('/api/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/api/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/api/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
});

// Admin management endpoints
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/api/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/api/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/api/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/api/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::put('/api/categories/{category}/status', [\App\Http\Controllers\CategoryController::class, 'updateStatus'])->name('categories.updateStatus');
    
    Route::get('/api/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('/api/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::put('/api/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/api/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/api/users/{user}/status', [\App\Http\Controllers\UserController::class, 'updateStatus'])->name('users.updateStatus');
});

// Rute publik yang bisa diakses semua
Route::get('/', function () {
    // Selalu tampilkan halaman login saat mengakses root
    return Inertia::render('login');
})->name('home');

// ==================================================
// GRUP ROUTE YANG DILINDUNGI BERDASARKAN ROLE
// ==================================================

// Grup untuk Admin


Route::middleware(['auth', 'role:admin', EnforceRolePrefix::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/laporan', function () {
        return Inertia::render('dashboard/admin/Laporan');
    })->name('laporan');

    Route::get('/{page?}', function ($page = 'Overview') {
        $component = collect(explode('/', trim($page, '/')))
            ->filter()
            ->map(fn($seg) => Str::of($seg)->replace('-', ' ')->title()->replace(' ', ''))
            ->implode('/');

        $candidate = 'dashboard/admin/' . ($component === '' ? 'Overview' : $component);
        $path = base_path('resources/js/pages/' . $candidate . '.vue');
        if (! file_exists($path)) {
            abort(404);
        }
        
        $props = [];
        if ($candidate === 'dashboard/admin/Overview') {
            $props = [
                'categories' => Category::all()->toArray(),
                'transactions' => FinancialTransaction::with('category')->get()->toArray(),
            ];
        }

        return Inertia::render($candidate, $props);
    })->where('page', '.*');
});

// Grup untuk Manajer
Route::middleware(['auth', 'role:manajer', EnforceRolePrefix::class])->prefix('manajer')->name('manajer.')->group(function () {
    Route::get('/laporan', function () {
        return Inertia::render('dashboard/manajer/Laporan');
    })->name('laporan');

   
    Route::get('/{page?}', function ($page = 'Overview') {
        $component = collect(explode('/', trim($page, '/')))
            ->filter()
            ->map(fn($seg) => Str::of($seg)->replace('-', ' ')->title()->replace(' ', ''))
            ->implode('/');

        $candidate = 'dashboard/manajer/' . ($component === '' ? 'Overview' : $component);
        $path = base_path('resources/js/pages/' . $candidate . '.vue');
        if (! file_exists($path)) {
            abort(404);
        }

        $props = [];
        if ($candidate === 'dashboard/manajer/Overview') {
            $props = [
                'categories' => Category::all()->toArray(),
                'transactions' => FinancialTransaction::with('category')->get()->toArray(),
            ];
        }

        return Inertia::render($candidate, $props);
    })->where('page', '.*');
});

// Grup untuk Staff
Route::middleware(['auth', 'role:staff', EnforceRolePrefix::class])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/laporan', function () {
        return Inertia::render('dashboard/staff/Laporan');
    })->name('laporan');

    Route::get('/{page?}', function ($page = 'Overview') {
        $component = collect(explode('/', trim($page, '/')))
            ->filter()
            ->map(fn($seg) => Str::of($seg)->replace('-', ' ')->title()->replace(' ', ''))
            ->implode('/');

        $candidate = 'dashboard/staff/' . ($component === '' ? 'Overview' : $component);
        $path = base_path('resources/js/pages/' . $candidate . '.vue'); 
        if (! file_exists($path)) {
            abort(404);
        }

        $props = [];
        if ($candidate === 'dashboard/staff/Manage') {
            $props = [
                'categories' => Category::all()->toArray(),
                'transactions' => FinancialTransaction::with('category')->get()->toArray(),
            ];
        } elseif ($candidate === 'dashboard/staff/Overview') {
            $props = [
                'categories' => Category::all()->toArray(),
                'transactions' => FinancialTransaction::with('category')->get()->toArray(),
            ];
        }

        return Inertia::render($candidate, $props);
    })->where('page', '.*');
});

// Grup untuk Viewer (tanpa autentikasi)
Route::prefix('viewer')->middleware('guest')->group(function () {
    Route::get('/laporan', function () {
        return Inertia::render('dashboard/viewer/Laporan');
    })->name('laporan');

    Route::get('/{page?}', function ($page = 'Overview') {
        $component = collect(explode('/', trim($page, '/')))
            ->filter()
            ->map(fn($seg) => Str::of($seg)->replace('-', ' ')->title()->replace(' ', ''))
            ->implode('/');

        $candidate = 'dashboard/viewer/' . ($component === '' ? 'Overview' : $component);
        $path = base_path('resources/js/pages/' . $candidate . '.vue');

        if (! file_exists($path)) {
            abort(404);
        }

        $props = [];
        if ($candidate === 'dashboard/viewer/Overview') {
            $props = [
                'categories' => Category::all()->toArray(),
                'transactions' => FinancialTransaction::with('category')->get()->toArray(),
            ];
        }

        return Inertia::render($candidate, $props);
    })->where('page', '.*');
});

require __DIR__.'/auth.php';
