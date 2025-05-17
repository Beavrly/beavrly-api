<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Helpers\GeminiHelper;
use App\Http\Controllers\Controller;
use App\Models\CustomCriterion;
use App\Models\Estimative;
use App\Models\Scope;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Smalot\PdfParser\Parser as PdfParser;

class EstimativeController extends Controller
{

    public function store(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return ApiResponse::validationError(['file' => 'Arquivo obrigatório.']);
            }

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = $file->store('estimatives');

            if (!in_array($extension, ['pdf', 'txt', 'md'])) {
                return ApiResponse::validationError(['file' => 'Formato não suportado. Use PDF, TXT ou MD.']);
            }

            $content = $extension === 'pdf'
                ? (new \Smalot\PdfParser\Parser())->parseFile($file->getRealPath())->getText()
                : file_get_contents($file->getRealPath());

            $response = GeminiHelper::extractEstimativeFromText($content);
            $cleanedResponse = preg_replace('/^```(?:json)?|```$/m', '', trim($response));

            $data = json_decode($cleanedResponse, true);

            if (!$data || !isset($data['estimates'])) {
                return ApiResponse::error('Formato de resposta inválido do Gemini.', 422, [
                    'raw_response' => $response,
                    'parsed_clean' => $cleanedResponse,
                    'json_error' => json_last_error_msg()
                ]);
            }

            $title = GeminiHelper::generateTitle($content);

            $estimative = new Estimative();
            $estimative->title = $title;
            $estimative->project_id = $request->project_id ?? 1;
            $estimative->type = 'system';
            $estimative->content = $response;
            $estimative->source_file = $filename;

            foreach (['optimistic', 'average', 'pessimistic'] as $level) {
                $estimative->{"dev_estimated_hours_$level"} = $data['estimates'][$level]['dev_hours'] ?? null;
                $estimative->{"design_estimated_hours_$level"} = $data['estimates'][$level]['design_hours'] ?? null;
                $estimative->{"qa_estimated_hours_$level"} = $data['estimates'][$level]['qa_hours'] ?? null;
                $estimative->{"avg_estimated_hours_$level"} = $data['estimates'][$level]['avg_hours'] ?? null;
                $estimative->{"total_value_$level"} = isset($data['estimates'][$level]['total_value']) 
                    ? intval($data['estimates'][$level]['total_value'] * 100) : null;
            }

            $estimative->approval = 'approved';
            $estimative->structured_risks = $data['risks'] ?? [];
            $estimative->status = 1;

            $estimative->considerations = implode("\n", [
                "Nível de complexidade: " . ($data['complexity_level'] ?? 'N/A'),
                "Fatores que influenciaram: " . implode(', ', $data['influencing_factors'] ?? []),
                "Recomendações: " . implode(', ', $data['recommendations'] ?? []),
                "Observações gerais: " . ($data['general_notes'] ?? 'N/A')
            ]);

            $estimative->save();

            return ApiResponse::success($estimative, 'Estimativa manual cadastrada com sucesso.', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return ApiResponse::error('Erro ao cadastrar estimativa manual.', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    public function generateFromScope(Scope $scope, Request $request)
    {
        try {
            $context = [
                'Número de funcionários' => $request->input('num_employees'),
                'Orçamento máximo' => $request->input('max_budget'),
                'Informações adicionais' => $request->input('additional_info'),
                'Valor hora' => $request->input('hourly_rate'),
            ];

            $criteriaFromRequest = $request->input('criteria', []);

            $response = GeminiHelper::generateEstimativeFromScope($scope->content, $context, $criteriaFromRequest);
            $cleanedResponse = preg_replace('/^```(?:json)?|```$/m', '', trim($response));

            $data = json_decode($cleanedResponse, true);

            if (!$data || !isset($data['estimates'])) {
                return ApiResponse::error('Formato de resposta inválido do Gemini.', 422, [
                    'raw_response' => $response,
                    'parsed_clean' => $cleanedResponse,
                    'json_error' => json_last_error_msg()
                ]);
            }

            $title = GeminiHelper::generateTitle($response);

            $estimative = new Estimative();
            $estimative->title = $title;
            $estimative->project_id = 1;
            $estimative->scope_id = $scope->id;
            $estimative->type = $request->input('type', 'system');
            $estimative->content = $response;

            foreach (['optimistic', 'average', 'pessimistic'] as $level) {
                $estimative->{"dev_estimated_hours_$level"} = $data['estimates'][$level]['dev_hours'] ?? null;
                $estimative->{"design_estimated_hours_$level"} = $data['estimates'][$level]['design_hours'] ?? null;
                $estimative->{"qa_estimated_hours_$level"} = $data['estimates'][$level]['qa_hours'] ?? null;
                $estimative->{"avg_estimated_hours_$level"} = $data['estimates'][$level]['avg_hours'] ?? null;
                $estimative->{"total_value_$level"} = isset($data['estimates'][$level]['total_value']) 
                    ? intval($data['estimates'][$level]['total_value'] * 100) : null;
            }

            $estimative->approval = 'pending';
            $estimative->additional_context = $context;
            $estimative->structured_risks = $data['risks'] ?? [];

            $estimative->considerations = implode("\n", [
                "Nível de complexidade: " . ($data['complexity_level'] ?? 'N/A'),
                "Fatores que influenciaram: " . implode(', ', $data['influencing_factors'] ?? []),
                "Recomendações: " . implode(', ', $data['recommendations'] ?? []),
                "Observações gerais: " . ($data['general_notes'] ?? 'N/A')
            ]);

            $estimative->status = 1;
            $estimative->save();

            foreach ($criteriaFromRequest as $item) {
                CustomCriterion::create([
                    'estimative_id' => $estimative->id,
                    'project_id' => $estimative->project_id,
                    'name' => $item['name'] ?? 'Sem nome',
                    'description' => $item['description'] ?? null
                ]);
            }

            return ApiResponse::success($estimative, 'Estimativa gerada com sucesso.', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao gerar estimativa.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function approve(Estimative $estimative)
    {
        try {
            $estimative->approval = 'approved';
            $estimative->save();

            return ApiResponse::success($estimative, 'Estimativa aprovada com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao aprovar estimativa.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function reject(Estimative $estimative)
    {
        try {
            $estimative->approval = 'rejected';
            $estimative->save();

            return ApiResponse::success($estimative, 'Estimativa reprovada com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao reprovar estimativa.', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function index()
    {
        try {
            $transcripts = Estimative::where('status', 1)->get();
            return ApiResponse::success($transcripts);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao listar transcrições.', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function show(Estimative $estimative)
    {
        if ($estimative->status !== 1) {
            return ApiResponse::error('Estimativa não encontrada ou inativa.', 404);
        }
    
        return ApiResponse::success($estimative);
    }


    public function remove(Estimative $estimative)
    {
        try {
            $estimative->status = 0;
            $estimative->save();
    
            return ApiResponse::success(null, 'Estimativa desativada com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao desativar escopo.', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
