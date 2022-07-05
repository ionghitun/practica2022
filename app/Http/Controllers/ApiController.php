<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 *
 */
class ApiController extends Controller
{
    /**
     * @param $results
     * @return JsonResponse
     */
    public function sendResponse($results): JsonResponse
    {
        $data = [
            'status' => true,
            'data' => $results,
            'message' => null,
            'errors' => []
        ];

        return response()->json($data);
    }

    /**
     * @param $message
     * @param array $errors
     * @return JsonResponse
     */
    public function sendError($message, array $errors = []): JsonResponse
    {
        $data = [
            'status' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors
        ];

        return response()->json($data);
    }
}
