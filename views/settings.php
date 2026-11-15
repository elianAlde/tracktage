<?php
    require_once __DIR__ . '/../middleware/auth.php';
    include __DIR__ . '/../components/head.php';

    $topBarTitle = t('nav.settings');
    $activePage = 'settings';
    include __DIR__ . '/../components/menu-top.php';

    $icons = [
        'bi-graph-up-arrow', 'bi-currency-bitcoin', 'bi-wallet2', 'bi-bank', 'bi-piggy-bank', 'bi-shield-check',
        'bi-car-front', 'bi-bicycle', 'bi-house', 'bi-building', 'bi-gem', 'bi-coin',
        'bi-briefcase', 'bi-credit-card', 'bi-cash-stack', 'bi-umbrella', 'bi-box-seam', 'bi-laptop',
    ];
    $colors = ['#0d6efd', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#f7931a', '#ffc107', '#198754', '#20c997', '#0dcaf0', '#6c757d', '#212529'];
?>

<div class="container app-container py-3">
    <div class="card p-3 mb-3 d-flex flex-row align-items-center gap-3">
        <div class="rounded-circle bg-body-tertiary d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
            <i class="bi bi-person fs-3 text-secondary"></i>
        </div>
        <div class="min-w-0">
            <div class="fw-semibold"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></div>
            <div class="text-muted small text-truncate"><?= htmlspecialchars($_SESSION['email']) ?></div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2 px-1" id="sections">
        <h6 class="fw-semibold mb-0"><?= te('set.sections') ?></h6>
        <a href="#" class="small text-decoration-none" id="btnAddCategory"><i class="bi bi-plus-lg" style="font-size:14px"></i> <?= te('set.new') ?></a>
    </div>
    <div class="card overflow-hidden mb-3" id="categoriesList"></div>

    <h6 class="fw-semibold mb-2 px-1"><?= te('set.appearance') ?></h6>
    <div class="card p-2 mb-3">
        <div class="btn-group w-100" role="group" id="themeGroup">
            <input type="radio" class="btn-check" name="theme" id="themeAuto" value="auto">
            <label class="btn btn-outline-primary btn-sm" for="themeAuto"><i class="bi bi-circle-half" style="font-size:14px"></i> <?= te('set.theme_auto') ?></label>
            <input type="radio" class="btn-check" name="theme" id="themeLight" value="light">
            <label class="btn btn-outline-primary btn-sm" for="themeLight"><i class="bi bi-sun" style="font-size:14px"></i> <?= te('set.theme_light') ?></label>
            <input type="radio" class="btn-check" name="theme" id="themeDark" value="dark">
            <label class="btn btn-outline-primary btn-sm" for="themeDark"><i class="bi bi-moon-stars" style="font-size:14px"></i> <?= te('set.theme_dark') ?></label>
        </div>
    </div>

    <h6 class="fw-semibold mb-2 px-1"><?= te('set.language') ?></h6>
    <div class="card p-2 mb-3">
        <div class="btn-group w-100" role="group" id="langGroup">
            <?php foreach (array_keys(LANGS) as $code): ?>
                <input type="radio" class="btn-check" name="lang" id="lang_<?= $code ?>" value="<?= $code ?>" <?= $code === LANG ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary btn-sm" for="lang_<?= $code ?>"><?= htmlspecialchars((require __DIR__ . "/../lang/$code.php")['lang.name']) ?></label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card overflow-hidden mb-3">
        <a href="#" class="list-row" data-bs-toggle="modal" data-bs-target="#modalPassword">
            <i class="bi bi-key" style="font-size:20px"></i><span class="flex-grow-1"><?= te('set.change_password') ?></span><i class="bi bi-chevron-right text-muted" style="font-size:14px"></i>
        </a>
        <a href="#" class="list-row" id="addToHomeBtn">
            <i class="bi bi-plus-square" style="font-size:20px"></i><span class="flex-grow-1"><?= te('set.add_home') ?></span><i class="bi bi-chevron-right text-muted" style="font-size:14px"></i>
        </a>
    </div>

    <h6 class="fw-semibold mb-2 px-1"><?= te('set.backup') ?></h6>
    <div class="card overflow-hidden mb-3">
        <a href="<?= BASE_URL ?>api/endpoint/backup/assets_csv.php" class="list-row" download>
            <i class="bi bi-filetype-csv" style="font-size:20px"></i>
            <div class="flex-grow-1"><div><?= te('set.backup_items') ?></div><div class="meta"><?= te('set.backup_items_desc') ?></div></div>
            <i class="bi bi-download text-muted" style="font-size:16px"></i>
        </a>
        <a href="<?= BASE_URL ?>api/endpoint/backup/history_csv.php" class="list-row" download>
            <i class="bi bi-filetype-csv" style="font-size:20px"></i>
            <div class="flex-grow-1"><div><?= te('set.backup_history') ?></div><div class="meta"><?= te('set.backup_history_desc') ?></div></div>
            <i class="bi bi-download text-muted" style="font-size:16px"></i>
        </a>
        <a href="<?= BASE_URL ?>api/endpoint/backup/database.php" class="list-row" download>
            <i class="bi bi-database-down" style="font-size:20px"></i>
            <div class="flex-grow-1"><div><?= te('set.backup_db') ?></div><div class="meta"><?= te('set.backup_db_desc') ?></div></div>
            <i class="bi bi-download text-muted" style="font-size:16px"></i>
        </a>
    </div>

    <div class="d-grid mt-4">
        <a href="<?= BASE_URL ?>logout" class="btn btn-outline-danger d-flex justify-content-center align-items-center">
            <i class="bi bi-box-arrow-right me-2" style="font-size:18px"></i> <?= te('set.logout') ?>
        </a>
    </div>
</div>

<!-- Section editor -->
<div class="modal fade" id="modalCategory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoryTitle"><?= te('set.new_section') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCategory" novalidate>
                    <input type="hidden" name="id" id="catId">
                    <input type="hidden" name="color" id="catColor" value="<?= $colors[0] ?>">
                    <div class="mb-3">
                        <label class="form-label" for="catName"><?= te('common.name') ?></label>
                        <input type="text" class="form-control" id="catName" name="name" maxlength="60" placeholder="<?= te('set.section_name_placeholder') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= te('set.kind') ?></label>
                        <select class="form-select" name="kind" id="catKind">
                            <option value="manual"><?= te('set.kind_manual') ?></option>
                            <option value="market"><?= te('set.kind_market') ?></option>
                        </select>
                        <div class="form-text" id="kindWarning"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="catIcon"><?= te('set.icon') ?></label>
                        <select class="form-select" name="icon" id="catIcon">
                            <?php foreach ($icons as $cls): ?>
                                <option value="<?= $cls ?>"><?= te('icon.' . $cls) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= te('set.color') ?></label>
                        <div class="d-flex flex-wrap gap-2" id="colorPicker">
                            <?php foreach ($colors as $c): ?>
                                <span class="color-swatch" data-color="<?= $c ?>" style="background:<?= $c ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="catLiability" name="is_liability" value="1">
                        <label class="form-check-label" for="catLiability"><?= te('set.liability_switch') ?></label>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger d-none" id="btnDeleteCategory"><?= te('common.delete') ?></button>
                <button type="submit" class="btn btn-primary ms-auto" form="formCategory"><?= te('common.save') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Change password -->
<div class="modal fade" id="modalPassword" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= te('set.change_password') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPassword" novalidate>
                    <input type="email" class="d-none" autocomplete="username" value="<?= htmlspecialchars($_SESSION['email']) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="current_password"><?= te('set.current_password') ?></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_password"><?= te('set.new_password') ?></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="10" autocomplete="new-password" required>
                        <div class="form-text"><?= te('set.password_min') ?></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" form="formPassword"><?= te('set.update') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- iOS "Add to Home Screen" instructions -->
<div class="modal fade" id="iosInstallModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title"><?= te('set.install_title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><?= te('set.install_intro') ?></p>
                <ol class="ps-3">
                    <li class="mb-2"><?= t('set.install_step1') ?> <i class="bi bi-box-arrow-up fs-5 mx-1"></i></li>
                    <li class="mb-2"><?= t('set.install_step2') ?> <i class="bi bi-plus-square fs-5 mx-1"></i></li>
                    <li><?= t('set.install_step3') ?></li>
                </ol>
                <div class="alert alert-secondary mt-3 small"><?= te('set.install_safari') ?></div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>script/settings.js"></script>
<?php include __DIR__ . '/../components/footer.php'; ?>
