<?php
/**
 * ImportLogRepository — Import Log Data Access
 *
 * Provides read access to the `import_logs` table and aggregated dashboard
 * statistics combining product, category, and log data.
 *
 * @package App\Repositories
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use PDO;

class ImportLogRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::local();
    }

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Returns the most recent import log entries, newest first.
     *
     * @param int $limit Maximum number of records to return (default 20, max 200).
     *
     * @return array<int,array<string,mixed>>
     */
    public function getRecent(int $limit = 20): array
    {
        $limit = max(1, min(200, $limit));

        $stmt = $this->db->prepare(
            'SELECT
                `id`,
                `started_at`,
                `completed_at`,
                `status`,
                `is_dry_run`,
                `triggered_by`,
                `user_id`,
                `records_inspected`,
                `records_inserted`,
                `records_updated`,
                `records_skipped`,
                `records_errored`,
                `error_summary`,
                `notes`
             FROM `import_logs`
             ORDER BY `started_at` DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a single import log entry by its primary key.
     *
     * @param int $id `import_logs.id`.
     *
     * @return array<string,mixed>|null  Row or null if not found.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                `id`,
                `started_at`,
                `completed_at`,
                `status`,
                `is_dry_run`,
                `triggered_by`,
                `user_id`,
                `records_inspected`,
                `records_inserted`,
                `records_updated`,
                `records_skipped`,
                `records_errored`,
                `error_summary`,
                `notes`
             FROM `import_logs`
             WHERE `id` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Returns aggregated statistics for the admin dashboard.
     *
     * Returned keys:
     *   - `total_products`    — total rows in `products`
     *   - `active_products`   — products where is_active_source = 1
     *   - `hidden_products`   — products where is_visible = 0
     *   - `parent_categories` — total rows in `parent_categories`
     *   - `child_categories`  — total rows in `child_categories`
     *   - `last_sync_at`      — completed_at of the most recent successful import log
     *   - `last_sync_status`  — status of the most recent import log (any status)
     *
     * @return array{
     *     total_products: int,
     *     active_products: int,
     *     hidden_products: int,
     *     parent_categories: int,
     *     child_categories: int,
     *     last_sync_at: string|null,
     *     last_sync_status: string|null
     * }
     */
    public function getDashboardStats(): array
    {
        // --- Product counts (single query with conditional aggregation) -----
        $row = $this->db
            ->query(
                'SELECT
                    COUNT(*)                                        AS total_products,
                    SUM(`is_active_source` = 1)                    AS active_products,
                    SUM(`is_visible` = 0)                          AS hidden_products
                 FROM `products`'
            )
            ->fetch(PDO::FETCH_ASSOC);

        // --- Category counts ------------------------------------------------
        $parentCount = (int) $this->db
            ->query('SELECT COUNT(*) FROM `parent_categories`')
            ->fetchColumn();

        $childCount = (int) $this->db
            ->query('SELECT COUNT(*) FROM `child_categories`')
            ->fetchColumn();

        // --- Last sync time from completed successful runs ------------------
        $lastSyncRow = $this->db
            ->query(
                'SELECT `completed_at`
                 FROM `import_logs`
                 WHERE `status` = \'completed\' AND `is_dry_run` = 0
                 ORDER BY `started_at` DESC
                 LIMIT 1'
            )
            ->fetch(PDO::FETCH_ASSOC);

        // --- Most recent log status (any) -----------------------------------
        $lastStatusRow = $this->db
            ->query(
                'SELECT `status`
                 FROM `import_logs`
                 ORDER BY `started_at` DESC
                 LIMIT 1'
            )
            ->fetch(PDO::FETCH_ASSOC);

        return [
            'total_products'    => (int) ($row['total_products']  ?? 0),
            'active_products'   => (int) ($row['active_products'] ?? 0),
            'hidden_products'   => (int) ($row['hidden_products'] ?? 0),
            'parent_categories' => $parentCount,
            'child_categories'  => $childCount,
            'last_sync_at'      => $lastSyncRow  ? $lastSyncRow['completed_at']  : null,
            'last_sync_status'  => $lastStatusRow ? $lastStatusRow['status']     : null,
        ];
    }
}
