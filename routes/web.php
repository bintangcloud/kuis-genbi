<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;

// Halaman Game Utama (Publik)
Route::get('/', [QuestionController::class, 'game'])->name('game');

// Halaman Admin CRUD (Dilindungi Breeze)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::get('/create', [QuestionController::class, 'create'])->name('create');
    Route::post('/store', [QuestionController::class, 'store'])->name('store');
    Route::get('/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
    Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
    Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');
});

// File Route bawaan Breeze (WAJIB ADA)
require __DIR__.'/auth.php';