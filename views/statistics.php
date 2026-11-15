<?php
	require_once __DIR__ . '/../middleware/auth.php';
	include __DIR__ . '/../components/head.php';

	$topBarTitle = 'Andamento';
	$activePage = 'statistics';
	include __DIR__ . '/../components/menu-top.php';
?>

<div class="container app-container py-3">
	<div class="btn-group w-100 mb-3" role="group" id="periodGroup">
		<button class="btn btn-outline-primary btn-sm" data-days="30">1M</button>
		<button class="btn btn-outline-primary btn-sm" data-days="90">3M</button>
		<button class="btn btn-outline-primary btn-sm active" data-days="365">1A</button>
		<button class="btn btn-outline-primary btn-sm" data-days="1825">5A</button>
		<button class="btn btn-outline-primary btn-sm" data-days="3650">Tutto</button>
	</div>

	<div class="card p-3 mb-3">
		<div class="d-flex justify-content-between align-items-baseline mb-2">
			<h6 class="fw-semibold mb-0">Patrimonio netto</h6>
			<span class="small" id="periodChange"></span>
		</div>
		<canvas id="netWorthChart" height="220"></canvas>
	</div>

	<div class="card p-3 mb-3">
		<h6 class="fw-semibold mb-2">Per sezione</h6>
		<canvas id="stackChart" height="240"></canvas>
	</div>

	<p class="text-muted small text-center px-3">
		Ogni giorno in cui apri l'app viene salvata una fotografia del patrimonio: il grafico si arricchisce con l'uso.
	</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>script/statistics.js"></script>
<?php include __DIR__ . '/../components/footer.php'; ?>
