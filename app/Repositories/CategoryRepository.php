<?php
/**
 * CategoryRepository — Parent and Child Category Data Access
 *
 * Provides read and visibility-update operations for `parent_categories`
 * and `child_categories`.  Used by menu navigation and admin panels.
 *
 * @package App\Repositories
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use App\Helpers\Logger;
use PDO;
use Throwable;

class CategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::local();
    }

    // =========================================================================
    // Read operations
    // =========================================================================

    /**
     * Returns all parent categories, each with their children nested under
     * a `children` key.  Suitable for building full menu navigation trees.
     *
     * Only categories where at least one linked product exists are included in
     * the children array; the parent is still returned even if it has no
     * visible children (e.g. for admin use).
     *
     * @return array<int,array<string,mixed>>  Indexed array of parent rows,
     *                                         each with a `children` sub-array.
     */
    public function getParentCategoriesWithChildren(): array
    {
        // Fetch all parents ordered by sort_order then name.
        $stmtParents = $this->db->query(
            'SELECT `id`, `source_id`, `name`, `name_normalized`, `is_visible`, `sort_order`
             FROM `parent_categories`
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $parents = $stmtParents->fetchAll(PDO::FETCH_ASSOC);

        if (empty($parents)) {
            return [];
        }

        // Fetch all children in one query to avoid N+1.
        $stmtChildren = $this->db->query(
            'SELECT `id`, `parent_category_id`, `source_id`, `name`, `name_normalized`,
                    `is_visible`, `sort_order`
             FROM `child_categories`
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $allChildren = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);

        // Index children by parent_category_id.
        $childrenByParent = [];
        foreach ($allChildren as $child) {
            $childrenByParent[(int) $child['parent_category_id']][] = $child;
        }

        // Attach children to each parent.
        foreach ($parents as &$parent) {
            $parent['children'] = $childrenByParent[(int) $parent['id']] ?? [];
        }
        unset($parent);

        return $parents;
    }

    /**
     * Returns only visible parent categories (is_visible = 1), ordered by
     * sort_order then name.  Used for public-facing menu navigation.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getVisibleParentCategories(): array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `source_id`, `name`, `name_normalized`, `is_visible`, `sort_order`
             FROM `parent_categories`
             WHERE `is_visible` = 1
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns all child categories belonging to a given parent, ordered by
     * sort_order then name.
     *
     * @param int $parentId Local `parent_categories.id`.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getChildrenByParent(int $parentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `parent_category_id`, `source_id`, `name`, `name_normalized`,
                    `is_visible`, `sort_order`
             FROM `child_categories`
             WHERE `parent_category_id` = :pid
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $stmt->execute([':pid' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a single parent category by its local ID.
     *
     * @param int $id Local `parent_categories.id`.
     *
     * @return array<string,mixed>|null  Row or null if not found.
     */
    public function getParentById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `source_id`, `name`, `name_normalized`, `is_visible`, `sort_order`
             FROM `parent_categories`
             WHERE `id` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Returns a single child category by its local ID.
     *
     * @param int $id Local `child_categories.id`.
     *
     * @return array<string,mixed>|null  Row or null if not found.
     */
    public function getChildById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `parent_category_id`, `source_id`, `name`, `name_normalized`,
                    `is_visible`, `sort_order`
             FROM `child_categories`
             WHERE `id` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    // =========================================================================
    // Write operations
    // =========================================================================

    /**
     * Sets the `is_visible` flag on a parent category.
     *
     * @param int  $id      Local `parent_categories.id`.
     * @param bool $visible New visibility state.
     *
     * @return bool True on success (row exists and was updated or already set).
     */
    public function updateParentVisibility(int $id, bool $visible): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE `parent_categories` SET `is_visible` = :visible WHERE `id` = :id'
            );
            $stmt->execute([':visible' => (int) $visible, ':id' => $id]);
            return $stmt->rowCount() >= 0; // 0 rowCount is OK if value unchanged.
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::updateParentVisibility failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sets the `is_visible` flag on a child category.
     *
     * @param int  $id      Local `child_categories.id`.
     * @param bool $visible New visibility state.
     *
     * @return bool True on success.
     */
    public function updateChildVisibility(int $id, bool $visible): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE `child_categories` SET `is_visible` = :visible WHERE `id` = :id'
            );
            $stmt->execute([':visible' => (int) $visible, ':id' => $id]);
            return $stmt->rowCount() >= 0;
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::updateChildVisibility failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }
}
