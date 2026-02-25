<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChatController;
use App\Http\Controllers\LogController;

Route::get('/', [ChatController::class, 'index'])->name('home');
Route::get('/c/{id}', [ChatController::class, 'index'])->name('chat.open');
Route::get('/chat/{id}/messages', [ChatController::class, 'show'])->name('chat.messages');
Route::get('/reports/{id}', [ChatController::class, 'downloadReport'])->name('chat.report');
Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::get('/chat/sidebar-history', [ChatController::class, 'historyPartial'])->name('chat.sidebar_history');
Route::get('/log-details', [LogController::class, 'show'])->name('logs.details');
Route::post('/log-details/clear', [LogController::class, 'clear'])->name('logs.clear');

Route::get('/migrate-db', function () {
    try {
        // This runs 'php artisan migrate --force'
        Artisan::call('migrate', ["--force" => true]);
        return "Database migration successful! <br><br> Logs: <br>" . Artisan::output();
    } catch (\Exception $e) {
        return "Migration failed: " . $e->getMessage();
    }
});
