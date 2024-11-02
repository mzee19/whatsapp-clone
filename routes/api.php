<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Chat\ChatroomController;
use App\Http\Controllers\Api\Chat\MessageController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/chatrooms', [ChatroomController::class, 'createChatroom']);
    Route::post('/chatrooms/{id}/enter', [ChatroomController::class, 'enterChatroom']);
    Route::post('/chatrooms/{id}/leave', [ChatroomController::class, 'leaveChatroom']);
    Route::post('/chatrooms/{id}/messages', [MessageController::class, 'sendMessage']);
    Route::get('/chatrooms/{id}/messages', [MessageController::class, 'listMessages']);
});
