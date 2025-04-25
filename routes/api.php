<?php

use Illuminate\Support\Facades\Route;


Route::POST('compare-dates', [\App\Http\Controllers\BotcakeHelperController::class, 'compareDates']);
Route::GET('next-thursday', [\App\Http\Controllers\BotcakeHelperController::class, 'getNextThursday']);
Route::GET('next-friday', [\App\Http\Controllers\BotcakeHelperController::class, 'getNextFriday']);

