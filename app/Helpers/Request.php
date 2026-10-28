<?php
/**
 * Request Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool { return self::method() === 'POST'; }
    public static function isGet(): bool  { return self::method() === 'GET';  }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /** Return sanitized string (strips tags, trims). */
    public static function str(string $key, string $default = ''): string
    {
        $v = self::input($key, $default);
        return trim(strip_tags((string)$v));
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::input($key, $default);
    }

    public static function clientIp(): string
    {
        $candidates = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($candidates as $h) {
            if (!empty($_SERVER[$h])) {
                return trim(explode(',', $_SERVER[$h])[0]);
            }
        }
        return '0.0.0.0';
    }

    public static function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public static function path(): string
    {
        return parse_url(self::uri(), PHP_URL_PATH) ?? '/';
    }
}

