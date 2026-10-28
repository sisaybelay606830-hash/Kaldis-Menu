<?php
/**
 * Application Logger
 * Writes to daily log files in storage/logs/.
 * Never logs passwords, credentials, or full DSN strings.
 */

declare(strict_types=1);

namespace App\Helpers;

class Logger
{
    private static string $logDir = '';

    public static function init(string $logDir): void
    {
        self::$logDir = rtrim($logDir, '/\\') . DIRECTORY_SEPARATOR;
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0750, true);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            self::write('DEBUG', $message, $context);
        }
    }

    private static function write(string $level, string $message, array $context): void
    {
        $dir  = self::$logDir ?: (dirname(__DIR__, 2) . '/storage/logs/');
        $file = $dir . date('Y-m-d') . '.log';
        $ctx  = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = sprintf(
            "[%s] [%s] %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            self::redact($message),
            $ctx
        );
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    private static function redact(string $msg): string
    {
        $msg = preg_replace('/password=[^\s;\'"]*/i', 'password=[REDACTED]', $msg) ?? $msg;
        $msg = preg_replace('/password:\s*\S+/i', 'password:[REDACTED]', $msg) ?? $msg;
        return $msg;
    }
}

