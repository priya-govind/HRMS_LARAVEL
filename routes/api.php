<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatBotController;

Route::post('/bot/handle-command', [ChatBotController::class, 'handleCommand']);