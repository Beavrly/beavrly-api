<?php

use App\Http\Controllers\api\v1\TranscriptionController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::get('/up', [TranscriptionController::class, 'test']);
});
