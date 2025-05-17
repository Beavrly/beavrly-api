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

class EstimativeController extends Controller
{

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

            $estimative = new Estimative();
            $estimative->project_id = 1;
            $estimative->scope_id = $scope->id;
            $estimative->type = $request->input('type', 'system');
            $estimative->content = $response;

            $estimative->dev_estimated_hours_optimistic = $data['estimates']['optimistic']['dev_hours'] ?? null;
            $estimative->design_estimated_hours_optimistic = $data['estimates']['optimistic']['design_hours'] ?? null;
            $estimative->qa_estimated_hours_optimistic = $data['estimates']['optimistic']['qa_hours'] ?? null;
            $estimative->avg_estimated_hours_optimistic = $data['estimates']['optimistic']['avg_hours'] ?? null;
            $estimative->total_value_optimistic = isset($data['estimates']['optimistic']['total_value']) 
                ? intval($data['estimates']['optimistic']['total_value'] * 100) : null;

            $estimative->dev_estimated_hours_pessimistic = $data['estimates']['pessimistic']['dev_hours'] ?? null;
            $estimative->design_estimated_hours_pessimistic = $data['estimates']['pessimistic']['design_hours'] ?? null;
            $estimative->qa_estimated_hours_pessimistic = $data['estimates']['pessimistic']['qa_hours'] ?? null;
            $estimative->avg_estimated_hours_pessimistic = $data['estimates']['pessimistic']['avg_hours'] ?? null;
            $estimative->total_value_pessimistic = isset($data['estimates']['pessimistic']['total_value']) 
                ? intval($data['estimates']['pessimistic']['total_value'] * 100) : null;

            $estimative->dev_estimated_hours_average = $data['estimates']['average']['dev_hours'] ?? null;
            $estimative->design_estimated_hours_average = $data['estimates']['average']['design_hours'] ?? null;
            $estimative->qa_estimated_hours_average = $data['estimates']['average']['qa_hours'] ?? null;
            $estimative->avg_estimated_hours_average = $data['estimates']['average']['avg_hours'] ?? null;
            $estimative->total_value_average = isset($data['estimates']['average']['total_value']) 
                ? intval($data['estimates']['average']['total_value'] * 100) : null;

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
