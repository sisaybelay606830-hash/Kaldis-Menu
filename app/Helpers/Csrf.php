<?php
/**
 * CSRF Token Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            Session::start();
        }
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function generate(): string
    {
        return self::token();
    }

    public static function field(string $name = '_csrf_token'): string
    {
        return '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . self::token() . '">';
    }

    public static function verify(?string $provided = null): bool
    {
        if ($provided === null) {
            $provided = $_POST['_csrf_token'] ?? $_POST['csrf_token'] ?? '';
        }
        $stored = $_SESSION[self::TOKEN_KEY] ?? '';
        if (empty($provided) || empty($stored)) {
            return false;
        }
        return hash_equals($stored, $provided);
    }

    public static function validateOrAbort(?string $provided = null): void
    {
        if (!self::verify($provided)) {
            Response::abort(403, 'Invalid or expired CSRF security token.');
        }
    }

    /** Regenerate token after use. */
    public static function regenerate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
}
