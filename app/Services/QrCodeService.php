<?php
/**
 * QR Code Service
 * Generates QR codes for public menu URLs using chillerlan/php-qrcode.
 */

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\Logger;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use RuntimeException;

class QrCodeService
{
    private string $outputDir;
    private string $baseUrl;

    public function __construct()
    {
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        $this->outputDir = rtrim($cfg['qr']['output_dir'], '/\\') . DIRECTORY_SEPARATOR;
        $this->baseUrl   = rtrim($cfg['qr']['base_url'], '/');

        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0750, true);
        }
    }

    // ---------------------------------------------------------------- Generate

    /**
     * Generate a general menu QR code.
     */
    public function generateGeneral(string $label = 'General Menu'): array
    {
        $url = $this->baseUrl;
        return $this->generate($label, 'general', $url, null);
    }

    /**
     * Generate a branch-specific QR code.
     *
     * @param int    $branchId   Local branch DB id
     * @param string $branchCode URL-safe branch code
     * @param string $label      Human-readable label for this QR
     */
    public function generateBranch(int $branchId, string $branchCode, string $label): array
    {
        // Use branch code (not internal ID) in the URL
        $url = $this->baseUrl . '?branch=' . urlencode($branchCode);
        return $this->generate($label, 'branch', $url, $branchId);
    }

    // ---------------------------------------------------------------- Internal

    private function generate(
        string  $label,
        string  $type,
        string  $url,
        ?int    $branchId,
        ?string $tableRef = null
    ): array {
        // Validate URL — must be http(s) and must not redirect to external domains
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException("Invalid QR target URL: {$url}");
        }
        $parsed = parse_url($url);
        $allowedHosts = parse_url($this->baseUrl, PHP_URL_HOST);
        if (($parsed['host'] ?? '') !== $allowedHosts) {
            throw new RuntimeException("QR URL host does not match configured base URL.");
        }

        // Build output file name
        $filename   = 'qr_' . $type . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        $outputPath = $this->outputDir . $filename;

        // Generate QR code
        $options = new QROptions([
            'version'       => 5,
            'outputType'    => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'      => QRCode::ECC_H,
            'scale'         => 8,
            'imageBase64'   => false,
            'addQuietzone'  => true,
            'quietzoneSize' => 4,
        ]);

        $qr = new QRCode($options);
        $qr->render($url, $outputPath);

        if (!file_exists($outputPath)) {
            throw new RuntimeException("QR code file was not created at: {$outputPath}");
        }

        Logger::info("QR code generated: {$filename} → {$url}");

        // Persist to DB
        $pdo  = Database::local();
        $stmt = $pdo->prepare(
            "INSERT INTO `qr_codes` (`label`, `type`, `branch_id`, `table_ref`, `public_url`, `file_path`, `is_active`)
             VALUES (:label, :type, :branch_id, :table_ref, :public_url, :file_path, 1)"
        );
        $stmt->execute([
            ':label'      => $label,
            ':type'       => $type,
            ':branch_id'  => $branchId,
            ':table_ref'  => $tableRef,
            ':public_url' => $url,
            ':file_path'  => $filename,
        ]);
        $id = (int) $pdo->lastInsertId();

        return [
            'id'        => $id,
            'label'     => $label,
            'type'      => $type,
            'url'       => $url,
            'file_path' => $filename,
            'full_path' => $outputPath,
        ];
    }

    // ---------------------------------------------------------------- Retrieve

    public function getById(int $id): ?array
    {
        $stmt = Database::local()->prepare(
            "SELECT q.*, b.name AS branch_name, b.code AS branch_code
               FROM `qr_codes` q
          LEFT JOIN `branches` b ON q.branch_id = b.id
              WHERE q.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(): array
    {
        return Database::local()->query(
            "SELECT q.*, b.name AS branch_name
               FROM `qr_codes` q
          LEFT JOIN `branches` b ON q.branch_id = b.id
           ORDER BY q.created_at DESC"
        )->fetchAll();
    }

    public function deactivate(int $id): void
    {
        Database::local()
            ->prepare("UPDATE `qr_codes` SET `is_active` = 0 WHERE `id` = ?")
            ->execute([$id]);
    }

    public function activate(int $id): void
    {
        Database::local()
            ->prepare("UPDATE `qr_codes` SET `is_active` = 1 WHERE `id` = ?")
            ->execute([$id]);
    }

    public function getFilePath(int $id): ?string
    {
        $row = $this->getById($id);
        if (!$row || !$row['file_path']) {
            return null;
        }
        $full = $this->outputDir . $row['file_path'];
        return file_exists($full) ? $full : null;
    }

    /** Return base64-encoded PNG data URI for inline preview. */
    public function getBase64Preview(int $id): ?string
    {
        $path = $this->getFilePath($id);
        if (!$path) {
            return null;
        }
        $data = file_get_contents($path);
        return $data ? 'data:image/png;base64,' . base64_encode($data) : null;
    }
}

