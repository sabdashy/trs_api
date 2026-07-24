<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function successResponse(
        $data = null,
        $message = 'Success',
        $status = 200
    ) {
        return response()->json([
            'success' => true,
            'code' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function errorResponse(
        $message = 'Error',
        $errors = null,
        $status = 400
    ) {
        return response()->json([
            'success' => false,
            'code' => $status,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    public function paginatedResponse(
        $paginatedData,
        $message = 'Transaction fetched successfully'
    ) {
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => $message,
            'data' => $paginatedData->items(),
            'pagination' => [
                'current_page' =>
                    $paginatedData->currentPage(),
                'last_page' =>
                    $paginatedData->lastPage(),
                'per_page' =>
                    $paginatedData->perPage(),
                'total' =>
                    $paginatedData->total(),
            ]
        ]);
    }
}