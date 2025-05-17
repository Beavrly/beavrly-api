<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success($data = [], string $message = 'Success', int $code = Response::HTTP_OK)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function error(string $message = 'An error occurred', int $code = Response::HTTP_INTERNAL_SERVER_ERROR, $errors = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    public static function unauthorized(string $message = 'Unauthorized')
    {
        return self::error($message, Response::HTTP_UNAUTHORIZED);
    }

    public static function validationError($errors, string $message = 'Validation failed')
    {
        return self::error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }
}
