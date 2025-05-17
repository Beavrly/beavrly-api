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
        Route::controller(TranscriptionController::class)->group(function(){
            Route::prefix('transcripts')->group(function(){
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{transcript}', 'show');
                Route::delete('/{transcript}', 'remove');
            });
        });
    
        // Escopos
        Route::controller(ScopeController::class)->group(function(){
            Route::prefix('scopes')->group(function(){
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{scope}', 'show');
                Route::delete('/{scope}', 'remove');

                Route::post('/{scope}/approve', 'approve');
                Route::post('/{scope}/reject', 'reject');
            });

            Route::post('/transcripts/{transcript}/generate-scope', 'generateFromTranscript');
        });

        // Estimativas
        Route::controller(EstimativeController::class)->group(function(){
            Route::prefix('estimatives')->group(function(){
                Route::get('/', 'index');
                Route::get('/{estimative}', 'show');
                Route::post('/', 'store');
                Route::delete('/{estimative}', 'remove');
                
                Route::post('/{estimative}/approve', 'approve');
                Route::post('/{estimative}/reject', 'reject');
            });

            Route::post('/scopes/{scope}/generate-estimative', 'generateFromScope');
        });
    
        // SETTINGS (opcional)
        // Route::get('/settings', [SettingController::class, 'index']);
        // Route::post('/settings', [SettingController::class, 'store']);
});