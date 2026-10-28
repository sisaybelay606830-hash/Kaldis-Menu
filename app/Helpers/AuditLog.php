<?php
/**
 * Audit Log Helper — records admin actions for traceability.
 */

declare(strict_types=1);

namespace App\Helpers;

class AuditLog
{
    public static function record(
        string  $action,
        ?string $entityType = null,
        ?int    $entityId   = null,
        array   $details    = [],
        ?int    $userId     = null
    ): void {
        try {
            $uid = $userId ?? (Session::isLoggedIn() ? Session::userId() : null);
            $ip  = Request::clientIp();
            $det = $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;

            Database::local()->prepare(
                "INSERT INTO `audit_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`)
                 VALUES (:uid, :action, :et, :ei, :details, :ip)"
            )->execute([
                ':uid'    => $uid,
                ':action' => $action,
                ':et'     => $entityType,
                ':ei'     => $entityId,
                ':details'=> $det,
                ':ip'     => $ip,
            ]);
        } catch (\Throwable $e) {
            Logger::error('AuditLog write failed: ' . $e->getMessage());
        }
    }
}

