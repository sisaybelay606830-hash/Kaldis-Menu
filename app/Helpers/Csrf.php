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
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    public static function verify(): bool
    {
        $posted = $_POST['_csrf_token'] ?? '';
        $stored = $_SESSION[self::TOKEN_KEY] ?? '';
        if (empty($posted) || empty($stored)) {
            return false;
        }
        return hash_equals($stored, $posted);
    }

    /** Regenerate token after use (optional, use for high-security forms). */
    public static function regenerate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
}

