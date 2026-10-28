<?php
/**
 * Settings Repository — key/value store backed by the settings table.
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;

class SettingsRepository
{
    private static array $cache = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        $stmt = Database::local()->prepare("SELECT `value` FROM `settings` WHERE `key` = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $value = ($row !== false) ? $row['value'] : $default;
        self::$cache[$key] = $value;
        return $value;
    }

    public function getAll(): array
    {
        $rows = Database::local()->query("SELECT `key`, `value`, `description` FROM `settings` ORDER BY `key`")->fetchAll();
        $out  = [];
        foreach ($rows as $r) {
            $out[$r['key']] = $r;
            self::$cache[$r['key']] = $r['value'];
        }
        return $out;
    }

    public function set(string $key, mixed $value): void
    {
        $stmt = Database::local()->prepare(
            "INSERT INTO `settings` (`key`, `value`) VALUES (:key, :val)
             ON DUPLICATE KEY UPDATE `value` = :val2"
        );
        $stmt->execute([':key' => $key, ':val' => $value, ':val2' => $value]);
        self::$cache[$key] = $value;
    }

    public function setMany(array $kvp): void
    {
        $pdo = Database::local();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO `settings` (`key`, `value`) VALUES (:key, :val)
                 ON DUPLICATE KEY UPDATE `value` = :val2"
            );
            foreach ($kvp as $key => $value) {
                $stmt->execute([':key' => $key, ':val' => $value, ':val2' => $value]);
                self::$cache[$key] = $value;
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

