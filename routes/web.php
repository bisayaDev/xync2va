<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
});

Route::get('/get-next-thursday', [\App\Http\Controllers\GetDateController::class, 'getNextThursday']);


