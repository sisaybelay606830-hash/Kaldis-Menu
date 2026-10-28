<?php
/**
 * Feedback Repository
 * Handles all database operations for customer feedback.
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use PDO;

class FeedbackRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::local();
    }

    /**
     * Create a new customer feedback record.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `feedbacks` (
                    `branch_id`,
                    `table_number`,
                    `rating_overall`,
                    `rating_coffee`,
                    `rating_food`,
                    `rating_service`,
                    `comment`,
                    `customer_name`,
                    `customer_phone`,
                    `is_reviewed`,
                    `created_at`
                ) VALUES (
                    :branch_id,
                    :table_number,
                    :rating_overall,
                    :rating_coffee,
                    :rating_food,
                    :rating_service,
                    :comment,
                    :customer_name,
                    :customer_phone,
                    0,
                    NOW()
                )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':branch_id'      => $data['branch_id'] ?? null,
            ':table_number'   => !empty($data['table_number']) ? trim((string)$data['table_number']) : null,
            ':rating_overall' => (int)($data['rating_overall'] ?? 5),
            ':rating_coffee'  => isset($data['rating_coffee']) && $data['rating_coffee'] !== '' ? (int)$data['rating_coffee'] : null,
            ':rating_food'    => isset($data['rating_food']) && $data['rating_food'] !== '' ? (int)$data['rating_food'] : null,
            ':rating_service' => isset($data['rating_service']) && $data['rating_service'] !== '' ? (int)$data['rating_service'] : null,
            ':comment'        => !empty($data['comment']) ? trim((string)$data['comment']) : null,
            ':customer_name'  => !empty($data['customer_name']) ? trim((string)$data['customer_name']) : null,
            ':customer_phone' => !empty($data['customer_phone']) ? trim((string)$data['customer_phone']) : null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get paginated feedback list with optional filters.
     *
     * @param array $filters ['branch_id' => int, 'rating' => int, 'reviewed' => bool|null, 'search' => string]
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['branch_id'])) {
            $where[] = 'f.branch_id = :branch_id';
            $params[':branch_id'] = (int)$filters['branch_id'];
        }

        if (!empty($filters['rating'])) {
            $where[] = 'f.rating_overall = :rating';
            $params[':rating'] = (int)$filters['rating'];
        }

        if (isset($filters['reviewed']) && $filters['reviewed'] !== '') {
            $where[] = 'f.is_reviewed = :is_reviewed';
            $params[':is_reviewed'] = (int)$filters['reviewed'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(f.comment LIKE :search_comment OR f.customer_name LIKE :search_name OR f.table_number LIKE :search_table)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[':search_comment'] = $searchTerm;
            $params[':search_name'] = $searchTerm;
            $params[':search_table'] = $searchTerm;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM `feedbacks` f WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch records
        $sql = "SELECT f.*, b.name AS branch_name, b.code AS branch_code
                  FROM `feedbacks` f
             LEFT JOIN `branches` b ON f.branch_id = b.id
                 WHERE {$whereClause}
              ORDER BY f.created_at DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Get aggregate statistics on feedback.
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) AS total_count,
                    COALESCE(AVG(rating_overall), 0) AS avg_overall,
                    COALESCE(AVG(rating_coffee), 0) AS avg_coffee,
                    COALESCE(AVG(rating_food), 0) AS avg_food,
                    COALESCE(AVG(rating_service), 0) AS avg_service,
                    SUM(CASE WHEN is_reviewed = 0 THEN 1 ELSE 0 END) AS unreviewed_count,
                    SUM(CASE WHEN rating_overall >= 4 THEN 1 ELSE 0 END) AS positive_count,
                    SUM(CASE WHEN rating_overall <= 2 THEN 1 ELSE 0 END) AS critical_count
                FROM `feedbacks`";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_count'      => 0,
            'avg_overall'      => 0,
            'avg_coffee'       => 0,
            'avg_food'         => 0,
            'avg_service'      => 0,
            'unreviewed_count' => 0,
            'positive_count'   => 0,
            'critical_count'   => 0,
        ];
    }

    /**
     * Toggle or update reviewed status.
     */
    public function toggleReviewed(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE `feedbacks` SET `is_reviewed` = 1 - `is_reviewed` WHERE `id` = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get single feedback record by ID.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT f.*, b.name AS branch_name, b.code AS branch_code
               FROM `feedbacks` f
          LEFT JOIN `branches` b ON f.branch_id = b.id
              WHERE f.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

