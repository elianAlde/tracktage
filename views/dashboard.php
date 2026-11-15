<?php
    require_once __DIR__ . '/../middleware/auth.php';
    include __DIR__ . '/../components/head.php';

    $topBarTitle = t('nav.networth');
    $activePage = 'dashboard';
    $topBarRefresh = true;
    include __DIR__ . '/../components/menu-top.php';
?>

<div class="container app-container py-3">
    <section class="card nw-hero mb-3">
        <div class="nw-label"><?= te('dash.net_worth') ?></div>
        <div class="nw-amount" id="netWorth">—</div>
        <div class="nw-change" id="netChange"></div>
        <div class="nw-spark d-none" id="sparkWrap"><canvas id="sparkline"></canvas></div>
        <div class="nw-split">
            <div><span><?= te('dash.assets') ?></span><strong class="text-success" id="totalAssets">—</strong></div>
            <div><span><?= te('dash.liabilities') ?></span><strong class="text-danger" id="totalLiabilities">—</strong></div>
        </div>
    </section>

    <div class="notice d-none mb-3" id="staleAlert" role="status">
        <i class="bi bi-clock-history"></i>
        <span id="staleText"></span>
    </div>

    <div class="card p-3 mb-3" id="allocationCard">
        <h6 class="fw-semibold mb-3"><?= te('dash.allocation') ?></h6>
        <div class="mx-auto" style="max-width: 240px;"><canvas id="allocationChart"></canvas></div>
        <ul class="alloc-legend" id="allocLegend"></ul>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <h6 class="fw-semibold mb-0"><?= te('dash.sections') ?></h6>
        <a href="<?= BASE_URL ?>settings#sections" class="small text-decoration-none"><?= te('dash.manage') ?></a>
    </div>
    <div class="card overflow-hidden" id="categoriesList">
        <div class="empty-state"><span class="spinner-border spinner-border-sm"></span></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>script/dashboard.js"></script>
<?php include __DIR__ . '/../components/footer.php'; ?>
