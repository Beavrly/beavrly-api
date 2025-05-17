<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Helpers\GeminiHelper;
use App\Http\Controllers\Controller;
use App\Models\Transcription;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Smalot\PdfParser\Parser as PdfParser;

class TranscriptionController extends Controller
{

    public function store(Request $request)
    {
        try {
            $content = '';

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = strtolower($file->getClientOriginalExtension());

                if ($extension === 'pdf') {
                    $parser = new PdfParser();
                    $pdf = $parser->parseFile($file->getRealPath());
                    $content = $pdf->getText();
                } else {
                    $content = file_get_contents($file->getRealPath());
                }

                $filename = $file->store('transcripts');
            } elseif ($request->filled('text')) {
                $content = $request->input('text');
                $filename = null;
            } else {
                return ApiResponse::error('Nenhum conteúdo foi enviado.', Response::HTTP_BAD_REQUEST);
            }

            $title = GeminiHelper::generateTitle($content);

            $transcript = Transcription::create([
                'content' => $content,
                'title' => $title,
                'source_file' => $filename ?? null,
                'project_id' => $request->project_id ?? 1,
            ]);

            return ApiResponse::success($transcript, 'Transcrição salva com sucesso.', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao salvar transcrição.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    
    public function index()
    {
        try {
            $transcripts = Transcription::where('status', 1)->get();
            return ApiResponse::success($transcripts);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao listar transcrições.', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function show(Transcription $transcript)
    {
        if ($transcript->status !== 1) {
            return ApiResponse::error('Transcrição não encontrada ou inativa.', 404);
        }
    
        return ApiResponse::success($transcript);
    }


    public function remove(Transcription $transcript)
    {
        try {
            $transcript->status = 0;
            $transcript->save();
    
            return ApiResponse::success(null, 'Transcrição desativada com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao desativar transcrição.', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
