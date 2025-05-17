<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Http\Request;
use Throwable;

class TranscriptionController extends Controller
{
    public function test(Request $request) {
        try{
            $client = new Client(env('GEMINI_API_KEY'));
            $response = $client->generativeModel(ModelName::GEMINI_1_5_FLASH)->generateContent(
                new TextPart('PHP in less than 100 chars'),
            );
            $text = $response->text();
            return response()->json([$text]);
        } catch (Throwable $e){
            return;
        }
    }
}
