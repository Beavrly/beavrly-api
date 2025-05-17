<?php
namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CustomCriterion;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class CustomCriterionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = CustomCriterion::query();

            if ($request->filled('estimative_id')) {
                $query->where('estimative_id', $request->input('estimative_id'));
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->input('project_id'));
            }

            $data = $query->get();
            return ApiResponse::success($data);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao listar critérios personalizados.', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $customCriterion = CustomCriterion::create([
                'criteria_id' => $request->input('criteria_id'),
                'estimative_id' => $request->input('estimative_id'),
                'project_id' => $request->input('project_id'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            return ApiResponse::success($customCriterion, 'Critério personalizado criado com sucesso.', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao criar critério personalizado.', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function destroy(CustomCriterion $customCriterion)
    {
        try {
            $customCriterion->delete();
            return ApiResponse::success(null, 'Critério personalizado removido com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao remover critério personalizado.', 500, ['exception' => $e->getMessage()]);
        }
    }
}

