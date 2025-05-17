<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Scope;
use App\Models\Transcript;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Helpers\GeminiHelper;
use App\Models\Transcription;
use Smalot\PdfParser\Parser as PdfParser;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class ScopeController extends Controller
{

    public function store(Request $request)
    {
        try {
            $content = '';
            $mdContent = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();

                if ($extension === 'pdf') {
                    $parser = new PdfParser();
                    $pdf = $parser->parseFile($file->getRealPath());
                    $content = $pdf->getText();
                } else {
                    $content = file_get_contents($file->getRealPath());
                }

                $prompt = <<<EOT
                    Você é um analista de requisitos de software. Converta o conteúdo bruto abaixo em um escopo técnico bem formatado, usando Markdown com:

                    - Títulos com `##` ou `###`
                    - Listas com `-`
                    - Ênfase com *itálico* ou **negrito**
                    - Quebras de seção claras

                    Retorne apenas o conteúdo final em Markdown. Nenhum comentário fora do escopo.

                    ---

                    Conteúdo bruto:
                    {$content}
                    EOT;

                $mdContent = (new GeminiHelper())
                    ->addMessage('user', $prompt)
                    ->generate();

                $filename = $file->store('scopes');
            } elseif ($request->has('text')) {
                $content = $request->input('text');
                $filename = null;
            } else {
                return ApiResponse::validationError(['file' => 'Arquivo ou texto é obrigatório.']);
            }

            $title = GeminiHelper::generateTitle($mdContent ?? $content);

            $scope = Scope::create([
                'content' => $mdContent ?? $content,
                'title' => $title,
                'source_file' => $filename ?? null,
                'project_id' => $request->project_id ?? 1,
                'transcript_id' => $request->transcript_id,
                'approval' => 'approved'
            ]);

            return ApiResponse::success($scope, 'Escopo cadastrado com sucesso.', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao cadastrar escopo.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function generateFromTranscript($transcriptId)
    {
        try {
            $transcript = Transcription::where('id', $transcriptId)
                ->where('status', 1)
                ->firstOrFail();

            $response = GeminiHelper::generateScopeFromTranscript($transcript->content);

            $title = GeminiHelper::generateTitle($response);

            $scope = Scope::create([
                'title' => $title,
                'content' => $response,
                'transcript_id' => $transcript->id,
                'project_id' => 1,
            ]);

            return ApiResponse::success($scope, 'Escopo gerado a partir da transcrição.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao gerar escopo.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    public function approve(Scope $scope)
    {
        try {
            $scope->approval = 'approved';
            $scope->save();

            return ApiResponse::success($scope, 'Escopo aprovada com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao aprovar escopo.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function reject(Scope $scope)
    {
        try {
            $scope->approval = 'rejected';
            $scope->save();

            return ApiResponse::success($scope, 'Escopo reprovada com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao reprovar escopo.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage()
            ]);
        }
    }
    
    public function index()
    {
        try {
            $transcripts = Scope::where('status', 1)->get();
            return ApiResponse::success($transcripts);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao listar transcrições.', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function show(Scope $scope)
    {
        if ($scope->status !== 1) {
            return ApiResponse::error('Escopo não encontrada ou inativa.', 404);
        }
    
        return ApiResponse::success($scope);
    }


    public function remove(Scope $scope)
    {
        try {
            $scope->status = 0;
            $scope->save();
    
            return ApiResponse::success(null, 'Escopo desativada com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao desativar escopo.', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
