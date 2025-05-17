<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Helpers\GeminiHelper;
use App\Http\Controllers\Controller;
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

            $response = GeminiHelper::generateEstimativeFromScope($scope->content, $context);
            $cleanedResponse = preg_replace('/^```(?:json)?|```$/m', '', trim($response));

            $data = json_decode($cleanedResponse, true);

            if (!$data || !isset($data['estimates'])) {
                return ApiResponse::error('Formato de resposta inválido do Gemini.', 422, [
                    'raw_response' => $response,
                    'parsed_clean' => $cleanedResponse,
                    'json_error' => json_last_error_msg()
                ]);
            }

            
            $estimative = Estimative::create([
                'project_id' => 1,
                'scope_id' => $scope->id,
                'type' => $request->input('type', 'system'),
                'content' => $response,
                'hourly_rate' => $data['hourly_rate'] ?? $request->input('hourly_rate'),
            
                'estimated_hours_optimistic'   => $data['estimates']['optimistic']['hours'] ?? null,
                'total_value_optimistic'   => isset($data['estimates']['optimistic']['total_value']) ? intval($data['estimates']['optimistic']['total_value'] * 100) : null,
            
                'estimated_hours_pessimistic' => $data['estimates']['pessimistic']['hours'] ?? null,
                'total_value_pessimistic'  => isset($data['estimates']['pessimistic']['total_value']) ? intval($data['estimates']['pessimistic']['total_value'] * 100) : null,
            
                'estimated_hours_average'     => $data['estimates']['average']['hours'] ?? null,
                'total_value_average'      => isset($data['estimates']['average']['total_value']) ? intval($data['estimates']['average']['total_value'] * 100) : null,

                'approval' => 'pending',
                'additional_context' => $context,
            
                'structured_risks' => $data['risks'] ?? [],
            
                'considerations' => implode("\n", [
                    "Nível de complexidade: " . ($data['complexity_level'] ?? 'N/A'),
                    "Fatores que influenciaram: " . implode(', ', $data['influencing_factors'] ?? []),
                    "Recomendações: " . implode(', ', $data['recommendations'] ?? []),
                    "Observações gerais: " . ($data['general_notes'] ?? 'N/A')
                ]),
                'status' => 1
            ]);

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
