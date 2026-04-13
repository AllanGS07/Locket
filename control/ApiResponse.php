<?php

class ApiResponse
{
    public static function success($data, $message = 'Sucesso', $statusCode = 200)
    {
        http_response_code($statusCode);
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ];
    }
    
    public static function error($message, $statusCode = 400, $errors = null)
    {
        http_response_code($statusCode);
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('c')
        ];
    }
    
    public static function send($response)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
