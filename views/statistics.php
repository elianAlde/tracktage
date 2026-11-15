<?php
	require_once __DIR__ . '/../middleware/auth.php';
	include __DIR__ . '/../components/head.php';

	$topBarTitle = t('nav.history');
	$activePage = 'statistics';
	include __DIR__ . '/../components/menu-top.php';
?>

<div class="container app-container py-3">
	<div class="btn-group w-100 mb-3" role="group" id="periodGroup">
		<button class="btn btn-outline-primary btn-sm" data-days="30"><?= te('stats.1m') ?></button>
		<button class="btn btn-outline-primary btn-sm" data-days="90"><?= te('stats.3m') ?></button>
		<button class="btn btn-outline-primary btn-sm active" data-days="365"><?= te('stats.1y') ?></button>
		<button class="btn btn-outline-primary btn-sm" data-days="1825"><?= te('stats.5y') ?></button>
		<button class="btn btn-outline-primary btn-sm" data-days="3650"><?= te('stats.all') ?></button>
	</div>

	<div class="card p-3 mb-3">
		<div class="d-flex justify-content-between align-items-baseline mb-2">
			<h6 class="fw-semibold mb-0"><?= te('stats.net_worth') ?></h6>
			<span class="small" id="periodChange"></span>
		</div>
		<canvas id="netWorthChart" height="220"></canvas>
	</div>

	<div class="card p-3 mb-3">
		<h6 class="fw-semibold mb-2"><?= te('stats.by_section') ?></h6>
		<canvas id="stackChart" height="240"></canvas>
	</div>

	<p class="text-muted small text-center px-3">
		<?= te('stats.footer') ?>
	</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>script/statistics.js"></script>
<?php include __DIR__ . '/../components/footer.php'; ?>
