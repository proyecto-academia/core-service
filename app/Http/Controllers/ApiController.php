<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{

    public function success($data = null, $message = 'success', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'url' => request()->url(),
        ], $statusCode);
    }

    /**
     * Respuesta de error.
     */
    public function error($message = 'failed', $statusCode = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
