<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-espresso">
            <i class="bi bi-chat-heart text-gold me-2"></i> Customer Feedback
        </h4>
        <p class="text-muted small mb-0">Ratings and reviews submitted by guests scanning QR codes or browsing online.</p>
    </div>
    <div>
        <a href="<?= base_url('admin/qrcodes') ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            <i class="bi bi-qr-code me-1"></i> Generate Feedback QR Code
        </a>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> <?= e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stats Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Overall Rating</span>
                <span class="badge bg-gold-subtle text-gold-dark rounded-pill">
                    <i class="bi bi-star-fill text-warning"></i>
                </span>
            </div>
            <h3 class="fw-bold text-espresso mb-1">
                <?= number_format((float)($stats['avg_overall'] ?? 0), 1) ?> <span class="fs-6 text-muted fw-normal">/ 5.0</span>
            </h3>
            <span class="extra-small text-muted"><?= (int)($stats['total_count'] ?? 0) ?> total reviews</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Coffee Rating</span>
                <i class="bi bi-cup-hot text-gold"></i>
            </div>
            <h3 class="fw-bold text-espresso mb-1">
                <?= number_format((float)($stats['avg_coffee'] ?? 0), 1) ?> <span class="fs-6 text-muted fw-normal">/ 5.0</span>
            </h3>
            <span class="extra-small text-muted">Quality of beverages</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Service Rating</span>
                <i class="bi bi-person-heart text-gold"></i>
            </div>
            <h3 class="fw-bold text-espresso mb-1">
                <?= number_format((float)($stats['avg_service'] ?? 0), 1) ?> <span class="fs-6 text-muted fw-normal">/ 5.0</span>
            </h3>
            <span class="extra-small text-muted">Hospitality & speed</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Pending Review</span>
                <span class="badge <?= ($stats['unreviewed_count'] ?? 0) > 0 ? 'bg-danger' : 'bg-success' ?> rounded-pill">
                    <?= (int)($stats['unreviewed_count'] ?? 0) ?>
                </span>
            </div>
            <h3 class="fw-bold text-espresso mb-1">
                <?= (int)($stats['unreviewed_count'] ?? 0) ?>
            </h3>
            <span class="extra-small text-muted">Awaiting management review</span>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="<?= base_url('admin/feedbacks') ?>" method="GET" class="row g-2 align-items-center">
            
            <div class="col-12 col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Search comments, customer..." 
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>

            <div class="col-6 col-md-3">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= e($b['id']) ?>" <?= ((int)($filters['branch_id'] ?? 0) === (int)$b['id']) ? 'selected' : '' ?>>
                            <?= e($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="rating" class="form-select form-select-sm">
                    <option value="">All Ratings</option>
                    <option value="5" <?= ((int)($filters['rating'] ?? 0) === 5) ? 'selected' : '' ?>>5 Stars ★★★★★</option>
                    <option value="4" <?= ((int)($filters['rating'] ?? 0) === 4) ? 'selected' : '' ?>>4 Stars ★★★★☆</option>
                    <option value="3" <?= ((int)($filters['rating'] ?? 0) === 3) ? 'selected' : '' ?>>3 Stars ★★★☆☆</option>
                    <option value="2" <?= ((int)($filters['rating'] ?? 0) === 2) ? 'selected' : '' ?>>2 Stars ★★☆☆☆</option>
                    <option value="1" <?= ((int)($filters['rating'] ?? 0) === 1) ? 'selected' : '' ?>>1 Star ★☆☆☆☆</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="reviewed" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="0" <?= (($filters['reviewed'] ?? '') === 0 || ($filters['reviewed'] ?? '') === '0') ? 'selected' : '' ?>>Pending Only</option>
                    <option value="1" <?= (($filters['reviewed'] ?? '') === 1 || ($filters['reviewed'] ?? '') === '1') ? 'selected' : '' ?>>Reviewed Only</option>
                </select>
            </div>

            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="<?= base_url('admin/feedbacks') ?>" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

        </form>
    </div>
</div>

<!-- Feedback Table -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">Date</th>
                    <th style="width: 140px;">Branch & Table</th>
                    <th style="width: 130px;">Overall</th>
                    <th style="width: 160px;">Breakdown</th>
                    <th>Customer Comment</th>
                    <th style="width: 140px;">Customer</th>
                    <th style="width: 120px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($feedbacks)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-dots display-6 d-block mb-2 text-muted opacity-50"></i>
                            No feedback records found matching your filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($feedbacks as $fb): ?>
                        <tr class="<?= empty($fb['is_reviewed']) ? 'table-warning bg-opacity-25' : '' ?>">
                            <td>
                                <span class="d-block fw-semibold small text-dark">
                                    <?= date('M d, Y', strtotime($fb['created_at'])) ?>
                                </span>
                                <span class="extra-small text-muted">
                                    <?= date('h:i A', strtotime($fb['created_at'])) ?>
                                </span>
                            </td>

                            <td>
                                <span class="fw-semibold small d-block text-espresso">
                                    <?= e($fb['branch_name'] ?? 'General / Not specified') ?>
                                </span>
                                <?php if (!empty($fb['table_number'])): ?>
                                    <span class="badge bg-light text-dark border extra-small">
                                        Table: <?= e($fb['table_number']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= $fb['rating_overall'] ? '-fill text-warning' : ' text-muted opacity-25' ?> extra-small"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="extra-small fw-bold <?= $fb['rating_overall'] >= 4 ? 'text-success' : ($fb['rating_overall'] <= 2 ? 'text-danger' : 'text-secondary') ?>">
                                    <?= (int)$fb['rating_overall'] ?> / 5
                                </span>
                            </td>

                            <td>
                                <div class="extra-small text-muted">
                                    <?php if (!empty($fb['rating_coffee'])): ?>
                                        <div><i class="bi bi-cup-hot text-gold me-1"></i>Coffee: <?= (int)$fb['rating_coffee'] ?>★</div>
                                    <?php endif; ?>
                                    <?php if (!empty($fb['rating_food'])): ?>
                                        <div><i class="bi bi-egg-fried text-gold me-1"></i>Food: <?= (int)$fb['rating_food'] ?>★</div>
                                    <?php endif; ?>
                                    <?php if (!empty($fb['rating_service'])): ?>
                                        <div><i class="bi bi-person text-gold me-1"></i>Service: <?= (int)$fb['rating_service'] ?>★</div>
                                    <?php endif; ?>
                                    <?php if (empty($fb['rating_coffee']) && empty($fb['rating_food']) && empty($fb['rating_service'])): ?>
                                        <span class="text-muted opacity-50">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($fb['comment'])): ?>
                                    <p class="mb-0 small text-dark fst-italic">“<?= e($fb['comment']) ?>”</p>
                                <?php else: ?>
                                    <span class="text-muted extra-small">No comment left</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($fb['customer_name'])): ?>
                                    <span class="d-block small fw-semibold text-dark"><?= e($fb['customer_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($fb['customer_phone'])): ?>
                                    <a href="tel:<?= e($fb['customer_phone']) ?>" class="extra-small text-muted text-decoration-none">
                                        <i class="bi bi-telephone me-1"></i><?= e($fb['customer_phone']) ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (empty($fb['customer_name']) && empty($fb['customer_phone'])): ?>
                                    <span class="text-muted extra-small">Anonymous</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">
                                <form action="<?= base_url('admin/feedbacks/' . $fb['id'] . '/toggle-reviewed') ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Csrf::generate() ?>">
                                    <?php if (empty($fb['is_reviewed'])): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Reviewed">
                                            <i class="bi bi-check2"></i> Mark Reviewed
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-light text-muted" title="Mark as Pending">
                                            <i class="bi bi-check2-all text-success me-1"></i> Reviewed
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-light border-0 d-flex align-items-center justify-content-between p-3">
            <span class="extra-small text-muted">
                Showing page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> items)
            </span>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/feedbacks?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

