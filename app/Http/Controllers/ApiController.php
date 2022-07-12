<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 *
 */
class ApiController extends Controller
{
    /**
     * @param $results
     * @param $responseCode
     * @return JsonResponse
     */
    public function sendResponse($results, $responseCode = Response::HTTP_OK): JsonResponse
    {
        $data = [
            'status' => true,
            'data' => $results,
            'message' => null,
            'errors' => []
        ];

        return response()->json($data, $responseCode);
    }

    /**
     * @param $message
     * @param array $errors
     * @param $responseCode
     * @return JsonResponse
     */
    public function sendError($message, array $errors = [], $responseCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        $data = [
            'status' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors
        ];

        return response()->json($data, $responseCode);
    }
}
