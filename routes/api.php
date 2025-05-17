<?php

use App\Http\Controllers\api\v1\CriterionController;
use App\Http\Controllers\api\v1\CustomCriterionController;
use App\Http\Controllers\api\v1\EstimativeController;
use App\Http\Controllers\api\v1\TranscriptionController;
use App\Http\Controllers\api\v1\ScopeController;
use Illuminate\Support\Facades\Route;



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

        // Critérios globais
        Route::controller(CriterionController::class)->group(function () {
            Route::prefix('criteria')->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{criterion}', 'show');
                Route::put('/{criterion}', 'update');
                Route::delete('/{criterion}', 'remove');
            });
        });

        // Critérios customizados
        Route::controller(CustomCriterionController::class)->group(function () {
            Route::prefix('custom-criteria')->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::delete('/{customCriterion}', 'remove');
            });
        });
    
});