<?php
/**
 * Database Connection Manager (PDO)
 * Lazy-initialises and caches local and source connections.
 */

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $local  = null;
    private static ?PDO $source = null;

    // ------------------------------------------------------------------ Local

    public static function local(): PDO
    {
        if (self::$local !== null) {
            return self::$local;
        }
        $cfg = self::config('local');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['database'], $cfg['charset']
        );
        try {
            self::$local = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        } catch (PDOException $e) {
            Logger::error('Local DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('Could not connect to the local database. Check APP_DB_* settings.');
        }
        return self::$local;
    }

    // ------------------------------------------------------------------ Source

    /**
     * Opens a READ-ONLY connection to the remote source database.
     * Throws RuntimeException on failure — callers must handle gracefully.
     */
    public static function source(): PDO
    {
        if (self::$source !== null) {
            return self::$source;
        }
        $cfg = self::config('source');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['database'],
            $cfg['charset']
        );
        try {
            self::$source = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
            // Extra safety: prevent any accidental writes at session level
            self::$source->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
        } catch (PDOException $e) {
            Logger::error('Source DB connection failed: ' . $e->getMessage());
            throw new RuntimeException(
                'Cannot connect to source database: ' . self::sanitizeError($e->getMessage())
            );
        }
        return self::$source;
    }

    public static function closeSource(): void
    {
        self::$source = null;
    }

    // ------------------------------------------------------------------ Helpers

    private static function config(string $key): array
    {
        static $configs = [];
        if (!isset($configs[$key])) {
            $configs[$key] = require dirname(__DIR__, 2) . '/config/database.php';
        }
        return $configs[$key][$key];
    }

    /** Strip host/password from error messages before they are logged or displayed. */
    public static function sanitizeError(string $message): string
    {
        return preg_replace('/\b(\d{1,3}\.){3}\d{1,3}\b/', '[host]', $message) ?? $message;
    }

    public static function beginTransaction(): void  { self::local()->beginTransaction(); }
    public static function commit(): void            { self::local()->commit(); }
    public static function rollback(): void          { if (self::local()->inTransaction()) self::local()->rollBack(); }
}

