<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;

class ExpenseCategoryApiController extends Controller
{
    /**
     * Get all expense categories
     */
    public function index(): JsonResponse
    {
        try {
            $categories = ExpenseCategory::select('id', 'name', 'created_at', 'updated_at', 'deleted_at')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function getAllCategories(): JsonResponse
    {
        try {
            $categories = ExpenseCategory::select('id', 'name', 'created_at', 'updated_at', 'deleted_at')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
    
}
