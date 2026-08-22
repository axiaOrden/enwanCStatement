<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShareStatementController;
use App\Http\Controllers\StatementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/statements');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('statements.index'))->name('dashboard');
    Route::get('/statements', [StatementController::class, 'index'])->name('statements.index');
    Route::get('/statements/preview', [StatementController::class, 'preview'])->name('statements.preview');
    Route::get('/statements/download', [StatementController::class, 'download'])->name('statements.download');
    Route::post('/statements/share', ShareStatementController::class)->name('statements.share');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
