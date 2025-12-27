<?php

namespace App\Http\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

trait ApiResponse
{
    protected static $response1 = [
        'code' => 200,
        'status' => true,
        'message' => null,
        'data' => null,
    ];

    protected static $response2 = [
        'code' => 200,
        'status' => true,
        'message' => null,
    ];

    public static function successResponseWithDataIndex($data_model = null, $data_resource = null, $message = null, $code = 200)
    {
        // self::$response1['code'] = $code;
        // self::$response1['status'] = true;
        // self::$response1['message'] = $message;
        // self::$response1['data'] = $data_resource;
        // if ($data_model instanceof LengthAwarePaginator || $data_model instanceof Paginator) {
        //     self::$response1['pagination'] = [
        //         'current_page' => $data_model->currentPage(),
        //         'per_page' => $data_model->perPage(),
        //         'total' => $data_model->total(),
        //         'last_page' => $data_model->lastPage()
        //     ];
        // } else {
        //     self::$response1['pagination'] = null;
        // }
        // return response()->json(self::$response1, self::$response1['code']);
        self::$response1['code'] = $code;
        self::$response1['status'] = true;
        self::$response1['message'] = $message;
        self::$response1['data'] = $data_resource;

        if ($data_model instanceof LengthAwarePaginator || $data_model instanceof Paginator) {
            self::$response1['pagination'] = [
                'current_page' => $data_model->currentPage(),
                'per_page' => $data_model->perPage(),
                'total' => $data_model->total(),
                'last_page' => $data_model->lastPage(),
            ];
        } elseif (is_array($data_model) && isset($data_model['current_page'])) {
            // Tangani pagination manual
            self::$response1['pagination'] = [
                'current_page' => $data_model['current_page'],
                'per_page' => $data_model['per_page'],
                'total' => $data_model['total'],
                'last_page' => $data_model['last_page'],
            ];
        } else {
            self::$response1['pagination'] = null;
        }

        return response()->json(self::$response1, self::$response1['code']);
    }

    public static function successResponseWithData($data = null, $message = null, $code = 200)
    {
        self::$response1['code'] = $code;
        self::$response1['status'] = true;
        self::$response1['message'] = $message;
        self::$response1['data'] = $data;

        return response()->json(self::$response1, self::$response1['code']);
    }

    public static function errorResponseWithData($errors = null, $message = null, $code = 400)
    {
        self::$response2['code'] = $code;
        self::$response2['status'] = false;
        self::$response2['message'] = $message;
        self::$response2['errors'] = $errors;

        return response()->json(self::$response2, self::$response2['code']);
    }

    public static function successResponse($message = null, $code = 200)
    {
        self::$response2['code'] = $code;
        self::$response2['status'] = true;
        self::$response2['message'] = $message;

        return response()->json(self::$response2, self::$response2['code']);
    }

    public static function errorResponse($message = null, $code = 400)
    {
        self::$response2['code'] = $code;
        self::$response2['status'] = false;
        self::$response2['message'] = $message;

        return response()->json(self::$response2, self::$response2['code']);
    }
}
