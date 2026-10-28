<?php
/**
 * Response Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Response
{
    public static function redirect(string $url, int $code = 302): never
    {
        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }

    public static function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function abort(int $code = 404, string $message = 'Not Found'): never
    {
        http_response_code($code);
        echo $message;
        exit;
    }
}

