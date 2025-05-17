<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Criterion;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class CriterionController extends Controller
{
    public function index()
    {
        try {
            $data = Criterion::all();
            return ApiResponse::success($data);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao listar critérios.', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $criterion = Criterion::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'is_global' => $request->filled('is_global') ? (bool) $request->input('is_global') : true
            ]);

            return ApiResponse::success($criterion, 'Critério criado com sucesso.', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao criar critério.', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function show(Criterion $criterion)
    {
        try {
            return ApiResponse::success($criterion);
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao exibir critério.', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function update(Request $request, Criterion $criterion)
    {
        try {
            $criterion->name = $request->input('name', $criterion->name);
            $criterion->description = $request->input('description', $criterion->description);
            $criterion->is_global = $request->filled('is_global') ? (bool) $request->input('is_global') : $criterion->is_global;
            $criterion->save();

            return ApiResponse::success($criterion, 'Critério atualizado com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao atualizar critério.', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function destroy(Criterion $criterion)
    {
        try {
            $criterion->delete();
            return ApiResponse::success(null, 'Critério removido com sucesso.');
        } catch (Exception $e) {
            return ApiResponse::error('Erro ao deletar critério.', 500, ['exception' => $e->getMessage()]);
        }
    }
}
