<?php
    require_once __DIR__ . '/../middleware/auth.php';
    require_once __DIR__ . '/../api/services/Portfolio.php';

    $category = (new Portfolio())->category((int)($_GET['id'] ?? 0));
    if (!$category) {
        header('Location: ' . BASE_URL . 'dashboard');
        exit;
    }
    $isMarket = $category['kind'] === 'market';

    include __DIR__ . '/../components/head.php';

    $topBarTitle = $category['name'];
    $activePage = 'dashboard';
    $topBarBack = BASE_URL . 'dashboard';
    $topBarAdd = true;
    include __DIR__ . '/../components/menu-top.php';
?>

<div class="container app-container py-3">
    <div class="card p-4 mb-3">
        <div class="d-flex align-items-center gap-3">
            <span class="cat-icon" style="background:<?= $category['color'] ?>1a;color:<?= $category['color'] ?>"><i class="bi <?= htmlspecialchars($category['icon']) ?>"></i></span>
            <div class="flex-grow-1">
                <div class="text-muted small"><?= te($category['is_liability'] ? 'cat.total_liabilities' : 'cat.total_value') ?></div>
                <div class="fs-3 fw-bold" id="categoryTotal">—</div>
                <?php if ($isMarket): ?>
                    <div class="small" id="categoryGain"></div>
                <?php endif; ?>
            </div>
            <?php if ($isMarket): ?>
                <button class="btn btn-soft btn-sm" id="btnRefresh" title="<?= te('top.refresh') ?>"><i class="bi bi-arrow-clockwise" style="font-size:18px"></i></button>
            <?php endif; ?>
        </div>
        <?php if ($isMarket): ?>
            <div class="text-muted small mt-2" id="pricesUpdated"></div>
        <?php endif; ?>
    </div>

    <div class="card overflow-hidden" id="assetsList">
        <div class="empty-state"><span class="spinner-border spinner-border-sm"></span></div>
    </div>
</div>

<!-- Add / edit item -->
<div class="modal fade" id="modalAsset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAssetTitle"><?= te('cat.add') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= te('common.close') ?>"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small py-2 d-flex gap-2 d-none" id="staleHint">
                    <i class="bi bi-clock-history" style="font-size:16px"></i><span></span>
                </div>
                <form id="formAsset" novalidate autocomplete="off">
                    <input type="hidden" name="id" id="assetId">
                    <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">

                    <?php if ($isMarket): ?>
                        <div class="mb-3 position-relative">
                            <label for="ticker" class="form-label"><?= te('cat.ticker_label') ?></label>
                            <input type="text" class="form-control text-uppercase" id="ticker" name="ticker" placeholder="<?= te('cat.ticker_placeholder') ?>" required>
                            <div class="list-group shadow search-results d-none" id="searchResults"></div>
                            <div class="form-text" id="tickerInfo"><?= te('cat.ticker_help') ?></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-3">
                                <label for="quantity" class="form-label"><?= te('cat.shares') ?></label>
                                <input type="text" inputmode="decimal" class="form-control" id="quantity" name="quantity" placeholder="0" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="avg_cost" class="form-label"><?= te('cat.avg_price') ?> <span class="text-muted small"><?= te('common.optional') ?></span></label>
                                <input type="text" inputmode="decimal" class="form-control" id="avg_cost" name="avg_cost" placeholder="<?= te('cat.per_share', ['currency' => BASE_CURRENCY]) ?>">
                            </div>
                        </div>
                        <div class="alert alert-secondary small py-2 d-none" id="previewValue"></div>
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= te('common.name') ?> <span class="text-muted small"><?= te('common.optional') ?></span></label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="120" placeholder="<?= te('cat.name_auto') ?>">
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= te('common.name') ?></label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="120" placeholder="<?= te('cat.name_placeholder') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="value" class="form-label"><?= te($category['is_liability'] ? 'cat.remaining_debt' : 'cat.value') ?> (<?= BASE_CURRENCY ?>)</label>
                            <input type="text" inputmode="decimal" class="form-control" id="value" name="value" placeholder="<?= LANG === 'it' ? '0,00' : '0.00' ?>" required>
                        </div>
                    <?php endif; ?>

                    <div class="mb-1">
                        <label for="notes" class="form-label"><?= te('common.notes') ?> <span class="text-muted small"><?= te('common.optional') ?></span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger d-none" id="btnDelete"><i class="bi bi-trash" style="font-size:16px"></i> <?= te('common.delete') ?></button>
                <button type="submit" class="btn btn-primary ms-auto" form="formAsset"><?= te('common.save') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    const CATEGORY = <?= json_encode([
        'id' => (int)$category['id'],
        'kind' => $category['kind'],
        'is_liability' => (bool)$category['is_liability'],
    ]) ?>;
</script>
<script src="<?= BASE_URL ?>script/category.js"></script>
<?php include __DIR__ . '/../components/footer.php'; ?>
