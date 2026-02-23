<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChatController;

Route::get('/', [ChatController::class, 'index']);
Route::get('/chat/new', [ChatController::class, 'newChat'])->name('chat.new');
Route::get('/chat/sidebar-history', [ChatController::class, 'historyPartial'])->name('chat.sidebar_history');
Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

Route::get('/migrate-db', function () {
    try {
        // This runs 'php artisan migrate --force'
        Artisan::call('migrate', ["--force" => true]);
        return "Database migration successful! <br><br> Logs: <br>" . Artisan::output();
    } catch (\Exception $e) {
        return "Migration failed: " . $e->getMessage();
    }
});