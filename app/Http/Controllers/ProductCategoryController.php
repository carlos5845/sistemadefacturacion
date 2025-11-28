<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        if (! $companyId) {
            return response()->json([
                'error' => 'Debe estar asociado a una empresa para crear categorías.',
                'message' => 'Debe estar asociado a una empresa para crear categorías.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Error de validación. Por favor, revise los campos.',
            ], 422);
        }

        try {
            $category = ProductCategory::create([
                'company_id' => $companyId,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating product category', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'company_id' => $companyId,
            ]);

            return response()->json([
                'error' => 'Ocurrió un error inesperado al crear la categoría: ' . $e->getMessage(),
                'message' => 'Error al crear la categoría. Por favor, intente nuevamente.',
            ], 500);
        }
    }
}
