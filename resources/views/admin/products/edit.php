<div class="row g-4">
    <!-- Left: Editable Admin Fields -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold text-espresso mb-0">Edit Product Presentation</h6>
                <span class="text-muted extra-small">Customize customer-facing details without affecting source records</span>
            </div>

            <div class="card-body p-4">
                <form action="<?= base_url('admin/products/' . $product['id']) ?>" method="POST" enctype="multipart/form-data">
                    <?= \App\Helpers\Csrf::field() ?>

                    <!-- Product Name (Source-managed, displayed for clarity) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-espresso">Product Name (Source Managed)</label>
                        <input type="text" class="form-control bg-light" value="<?= e($product['name']) ?>" readonly disabled>
                        <div class="form-text extra-small">Original name from <code>article_import</code>. Cannot be edited directly here.</div>
                    </div>

                    <!-- Menu Visibility -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="is_visible" 
                                name="is_visible" 
                                value="1" 
                                <?= (bool)$product['is_visible'] ? 'checked' : '' ?>
                            >
                            <label class="form-check-label fw-semibold text-espresso" for="is_visible">
                                Visible on Public Menu
                            </label>
                        </div>
                        <div class="form-text extra-small">When disabled, this product is immediately hidden from customers.</div>
                    </div>

                    <!-- Verified Price & VAT calculation -->
                    <div class="mb-3">
                        <label for="price" class="form-label small fw-semibold text-espresso">Manual Override Selling Price (ETB)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">ETB</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                class="form-control" 
                                id="price" 
                                name="price" 
                                value="<?= $product['price'] !== null ? e((string)$product['price']) : '' ?>" 
                                placeholder="<?= !empty($product['default_value']) ? number_format((float)$product['default_value'] * ($vatRate ?? 1.20), 2, '.', '') : 'e.g. 85.00' ?>"
                            >
                        </div>
                        <?php if (!empty($product['default_value']) && is_numeric($product['default_value'])): ?>
                            <div class="p-2 mt-2 bg-light rounded border extra-small text-muted">
                                <i class="bi bi-calculator text-gold me-1"></i>
                                <strong>System VAT Price:</strong> Base ERP <code>default_value</code> (<?= number_format((float)$product['default_value'], 2) ?> ETB) &times; VAT factor (<?= number_format((float)($vatRate ?? 1.20), 2) ?>) = <strong class="text-success"><?= number_format((float)$product['default_value'] * ($vatRate ?? 1.20), 2) ?> ETB</strong>.
                                <br><span class="text-secondary">If the override input is empty, this VAT-calculated price is automatically displayed on the digital menu. Enter a price above only if you wish to override it manually.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Local Description -->
                    <div class="mb-3">
                        <label for="local_description" class="form-label small fw-semibold text-espresso">Customer Description</label>
                        <textarea 
                            class="form-control" 
                            id="local_description" 
                            name="local_description" 
                            rows="4" 
                            placeholder="Enter appetizing, customer-facing product description, ingredients, or allergens..."
                        ><?= e($product['local_description'] ?? '') ?></textarea>
                        <div class="form-text extra-small">This text is displayed on the customer product card.</div>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-4">
                        <label for="image" class="form-label small fw-semibold text-espresso">Product Image</label>
                        <?php if (!empty($product['image_path']) && file_exists(BASE_PATH . '/public/' . ltrim($product['image_path'], '/'))): ?>
                            <div class="mb-2 d-flex align-items-center gap-3 p-2 border rounded-3 bg-light">
                                <img src="<?= base_url($product['image_path']) ?>" alt="" style="width: 70px; height: 70px; object-fit: cover;" class="rounded-2">
                                <div>
                                    <div class="small fw-semibold text-espresso">Current Image</div>
                                    <span class="text-muted extra-small">Upload a new file below to replace it.</span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text extra-small">Formats: JPG, PNG, WebP. Maximum size: <?= \App\Helpers\Env::getInt('UPLOAD_MAX_SIZE_MB', 5) ?>MB.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-gold px-4 rounded-pill fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                        <a href="<?= base_url('admin/products') ?>" class="btn btn-outline-secondary px-3 rounded-pill">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Source Database Metadata (Read-Only) -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold text-espresso mb-0"><i class="bi bi-database me-2 text-gold"></i>Source Database Traceability</h6>
                <span class="text-muted extra-small">Synced from <code>article_import</code> table</span>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless small mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Article Code:</td>
                        <td><code class="fw-bold text-espresso"><?= e($product['article_code']) ?></code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Parent Preference:</td>
                        <td>
                            <strong><?= e($product['src_parent_pref'] ?? 'None') ?></strong>
                            <span class="text-muted extra-small">(ID: <?= e((string)($product['src_parent_pref_id'] ?? 'NULL')) ?>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Child Preference:</td>
                        <td>
                            <strong><?= e($product['src_child_pref'] ?? 'None') ?></strong>
                            <span class="text-muted extra-small">(ID: <?= e((string)($product['src_child_pref_id'] ?? 'NULL')) ?>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Consignee Unit:</td>
                        <td><?= e($product['consignee_unit'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">GSL Type:</td>
                        <td><?= e($product['gsl_type'] ?? 'N/A') ?> (ID: <?= e((string)($product['gsl_type_id'] ?? 'NULL')) ?>)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Default Value:</td>
                        <td><code><?= e($product['default_value'] ?? 'NULL') ?></code> <span class="badge bg-warning text-dark extra-small">Raw Field</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Source Active:</td>
                        <td>
                            <?php if ((bool)$product['is_active_source']): ?>
                                <span class="badge bg-success">Active (1)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive (0)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Synced:</td>
                        <td><?= !empty($product['synced_at']) ? date('Y-m-d H:i:s', strtotime($product['synced_at'])) : 'Sample Record' ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-gold-subtle border border-gold-light">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-shield-check text-gold fs-5"></i>
                    <div class="extra-small text-gold-dark">
                        <strong>Protection of Admin Overrides:</strong> Subsequent sync operations from <code>article_import</code> will update source fields but will <em>never</em> overwrite your custom description, visibility toggle, or uploaded image.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

