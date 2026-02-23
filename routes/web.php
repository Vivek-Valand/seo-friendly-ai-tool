<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChatController;

Route::get('/', [ChatController::class, 'index']);
Route::get('/chat/new', [ChatController::class, 'newChat'])->name('chat.new');
Route::get('/chat/sidebar-history', [ChatController::class, 'historyPartial'])->name('chat.sidebar_history');
Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
