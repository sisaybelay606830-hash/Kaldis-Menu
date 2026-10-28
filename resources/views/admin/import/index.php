<div class="row g-4">
    <!-- Import Controller Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold text-espresso mb-0"><i class="bi bi-arrow-repeat me-2 text-gold"></i>Synchronize Products from Source</h6>
                <span class="text-muted extra-small">Reads from <code>article_import</code> table on remote MySQL server</span>
            </div>

            <div class="card-body p-4">
                <!-- Source Connection Status Badge -->
                <div class="p-3 rounded-3 mb-4 <?= $sourceStatus['connected'] ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : 'bg-warning bg-opacity-10 border border-warning border-opacity-50' ?>">
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($sourceStatus['connected']): ?>
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                <strong class="text-success small">Source Database Online</strong>
                                <div class="text-muted extra-small"><?= e($sourceStatus['message']) ?></div>
                            </div>
                        <?php else: ?>
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                            <div>
                                <strong class="text-dark small">Source Connection Notice</strong>
                                <div class="text-muted extra-small"><?= e($sourceStatus['message']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <form action="<?= base_url('admin/import/run') ?>" method="POST" id="importForm" onsubmit="return confirmSync();">
                    <?= \App\Helpers\Csrf::field() ?>

                    <div class="form-check form-switch mb-3 p-3 bg-light rounded-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" id="dryRunCheck" name="dry_run" value="1" checked>
                        <label class="form-check-label fw-bold text-espresso" for="dryRunCheck">
                            Dry-Run Mode (Simulation Only)
                        </label>
                        <div class="form-text extra-small text-muted ps-4">
                            When enabled, inspects records from source and logs counts, but makes <strong>no modifications</strong> to your local database. Recommended before live synchronizations.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" id="syncBtn" class="btn btn-gold rounded-pill px-4 py-2 fw-semibold">
                            <i class="bi bi-play-circle me-1"></i> Start Synchronization
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sync Policy & Protection Details -->
        <div class="card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold text-espresso mb-2"><i class="bi bi-shield-check text-success me-1"></i> Synchronization Integrity Rules</h6>
                <ul class="text-muted extra-small mb-0 ps-3">
                    <li class="mb-1"><strong>Upsert Strategy:</strong> Existing products are updated using <code>article_code</code> as the unique key; new products are inserted.</li>
                    <li class="mb-1"><strong>Admin Preservation:</strong> Local descriptions, custom uploaded photos, verified prices, and visibility toggles are <em>never</em> overwritten.</li>
                    <li class="mb-1"><strong>Category Deduplication:</strong> Normalized matching prevents duplicate parent/child categories caused by whitespace or casing differences.</li>
                    <li class="mb-1"><strong>Concurrency Protection:</strong> A file lock prevents overlapping import operations.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Import Logs -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold text-espresso mb-0">Synchronization History</h6>
                    <span class="text-muted extra-small">Detailed log of past synchronization runs</span>
                </div>
                <a href="<?= base_url('admin/import/logs') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 extra-small">Full History</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Inspected</th>
                            <th>Inserted</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentLogs)): ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-espresso"><?= date('M j, H:i', strtotime($log['started_at'])) ?></div>
                                        <span class="text-muted extra-small"><?= e($log['triggered_by']) ?><?= (bool)$log['is_dry_run'] ? ' (dry)' : '' ?></span>
                                    </td>
                                    <td>
                                        <?php if ($log['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php elseif ($log['status'] === 'failed'): ?>
                                            <span class="badge bg-danger" title="<?= e($log['error_summary'] ?? '') ?>">Failed</span>
                                        <?php elseif ($log['status'] === 'running'): ?>
                                            <span class="badge bg-warning text-dark">Running</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= e($log['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($log['records_inspected']) ?></td>
                                    <td class="text-success fw-bold">+<?= number_format($log['records_inserted']) ?></td>
                                    <td class="text-info">~<?= number_format($log['records_updated']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No synchronization logs available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmSync() {
    const isDry = document.getElementById('dryRunCheck').checked;
    const msg = isDry 
        ? "Run DRY-RUN simulation? (No changes will be written to local database)"
        : "Proceed with LIVE SYNCHRONIZATION? This will import/update records in the local catalog.";
    return confirm(msg);
}
</script>

