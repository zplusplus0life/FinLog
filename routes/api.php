<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public export routes
Route::get('export/pdf', [\App\Http\Controllers\ExportController::class, 'downloadPdf'])->name('export.pdf');

// API Authentication for mobile/external apps
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/me', function () {
        return response()->json(auth()->user());
    });

    // Financial data API for mobile apps
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);
    Route::get('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'index']);
    Route::post('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'store']);

    // ROLE-BASED FILE ACCESS ROUTES
    Route::middleware('secure.file')->prefix('files')->name('files.')->group(function () {
        Route::get('/list', [\App\Http\Controllers\FileAccessController::class, 'listFiles'])->name('list');
        Route::post('/upload', [\App\Http\Controllers\FileAccessController::class, 'uploadFile'])->name('upload');
        Route::delete('/{filename}', [\App\Http\Controllers\FileAccessController::class, 'deleteFile'])->where('filename', '[^/]+')->name('delete');
    });
});
