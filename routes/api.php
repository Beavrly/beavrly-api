<?php

use App\Http\Controllers\api\v1\EstimativeController;
use App\Http\Controllers\api\v1\TranscriptionController;
use App\Http\Controllers\api\v1\ScopeController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::get('/up', [TranscriptionController::class, 'test']);
});

Route::prefix('v1')->group(function () {

        // Transcrições
        Route::get('/transcripts', [TranscriptionController::class, 'index']);
        Route::get('/transcripts/{transcript}', [TranscriptionController::class, 'show']);
        Route::post('/transcripts', [TranscriptionController::class, 'store']);
        Route::delete('/transcripts/{transcript}', [TranscriptionController::class, 'remove']);
    
        // Escopos
        Route::get('/scopes', [ScopeController::class, 'index']);
        Route::get('/scopes/{scope}', [ScopeController::class, 'show']);
        Route::post('/scopes', [ScopeController::class, 'store']);
        Route::delete('/scopes/{scope}', [ScopeController::class, 'remove']);
        Route::post('/transcripts/{transcript}/generate-scope', [ScopeController::class, 'generateFromTranscript']);

        Route::post('/scopes/{scope}/approve', [ScopeController::class, 'approve']);
        Route::post('/scopes/{scope}/reject', [ScopeController::class, 'reject']);


        // Estimativas
        Route::get('/estimatives', [EstimativeController::class, 'index']);
        Route::get('/estimatives/{estimative}', [EstimativeController::class, 'show']);
        Route::post('/estimatives', [EstimativeController::class, 'store']);
        Route::delete('/estimatives/{estimative}', [EstimativeController::class, 'remove']);
        Route::post('/scopes/{scope}/generate-estimative', [EstimativeController::class, 'generateFromScope']);

        Route::post('/estimatives/{estimative}/approve', [EstimativeController::class, 'approve']);
        Route::post('/estimatives/{estimative}/reject', [EstimativeController::class, 'reject']);
    
        // SETTINGS (opcional)
        // Route::get('/settings', [SettingController::class, 'index']);
        // Route::post('/settings', [SettingController::class, 'store']);
});