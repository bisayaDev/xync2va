<?php

use Illuminate\Support\Facades\Route;


Route::POST('compare-dates', [\App\Http\Controllers\BotcakeHelperController::class, 'compareDates']);
Route::POST('assign-value', [\App\Http\Controllers\BotcakeHelperController::class, 'assignValue']);

Route::GET('next-thursday', [\App\Http\Controllers\BotcakeHelperController::class, 'getNextThursday']);
Route::GET('next-friday', [\App\Http\Controllers\BotcakeHelperController::class, 'getNextFriday']);

Route::GET('meeting/{passcode}', [\App\Http\Controllers\MeetingController::class, 'getMeeting']);
