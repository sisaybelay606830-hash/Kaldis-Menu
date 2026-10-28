<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold text-espresso mb-0">Synchronization Log History</h6>
                    <span class="text-muted extra-small">Audit records of all imports, sync attempts, and error details</span>
                </div>
                <a href="<?= base_url('admin/import') ?>" class="btn btn-sm btn-outline-gold rounded-pill px-3 py-1">
                    <i class="bi bi-arrow-left me-1"></i> Back to Sync
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Log ID</th>
                            <th>Started / Completed</th>
                            <th>Mode / Trigger</th>
                            <th>Status</th>
                            <th>Inspected</th>
                            <th>Inserted</th>
                            <th>Updated</th>
                            <th>Skipped</th>
                            <th>Errored</th>
                            <th>Error Details / Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $l): ?>
                                <tr>
                                    <td><code>#<?= $l['id'] ?></code></td>
                                    <td>
                                        <div class="fw-semibold text-espresso"><?= date('Y-m-d H:i:s', strtotime($l['started_at'])) ?></div>
                                        <?php if (!empty($l['completed_at'])): ?>
                                            <span class="text-muted extra-small">End: <?= date('H:i:s', strtotime($l['completed_at'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($l['triggered_by']) ?></span>
                                        <?php if ((bool)$l['is_dry_run']): ?>
                                            <span class="badge bg-info text-dark">Dry Run</span>
                                        <?php else: ?>
                                            <span class="badge bg-dark">Live</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($l['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($l['status'] === 'failed'): ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php elseif ($l['status'] === 'running'): ?>
                                            <span class="badge bg-warning text-dark">Running</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= e($l['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($l['records_inspected']) ?></td>
                                    <td class="text-success fw-bold">+<?= number_format($l['records_inserted']) ?></td>
                                    <td class="text-info">~<?= number_format($l['records_updated']) ?></td>
                                    <td class="text-muted"><?= number_format($l['records_skipped']) ?></td>
                                    <td>
                                        <?php if ((int)$l['records_errored'] > 0): ?>
                                            <span class="text-danger fw-bold"><?= $l['records_errored'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($l['error_summary'])): ?>
                                            <span class="text-danger extra-small d-block text-truncate" style="max-width: 250px;" title="<?= e($l['error_summary']) ?>">
                                                <?= e($l['error_summary']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted extra-small">None</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">No synchronization logs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

