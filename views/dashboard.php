<?php
	require_once __DIR__ . '/../middleware/auth.php';
	include __DIR__ . '/../components/head.php';

	$topBarTitle = 'Patrimonio';
	$activePage = 'dashboard';
	$topBarRefresh = true;
	include __DIR__ . '/../components/menu-top.php';
?>

<div class="container app-container py-3">
	<!-- TOTALE -->
	<div class="card hero-card p-4 mb-3">
		<div class="label">Patrimonio netto</div>
		<div class="amount" id="netWorth">—</div>
		<div class="d-flex gap-4 mt-2 small">
			<div><span class="label">Attività</span><br><strong id="totalAssets">—</strong></div>
			<div><span class="label">Passività</span><br><strong id="totalLiabilities">—</strong></div>
		</div>
	</div>

	<!-- VALORI DA AGGIORNARE -->
	<div class="alert alert-warning d-none d-flex align-items-center gap-2 py-2 small" id="staleAlert" role="alert">
		<i class="bi bi-clock-history" style="font-size:18px"></i>
		<span id="staleText"></span>
	</div>

	<!-- ALLOCAZIONE -->
	<div class="card p-3 mb-3" id="allocationCard">
		<h6 class="fw-semibold mb-3">Allocazione</h6>
		<div class="mx-auto" style="max-width: 260px;"><canvas id="allocationChart"></canvas></div>
	</div>

	<!-- SEZIONI -->
	<div class="d-flex justify-content-between align-items-center mb-2 px-1">
		<h6 class="fw-semibold mb-0">Sezioni</h6>
		<a href="<?= BASE_URL ?>settings#sections" class="small text-decoration-none">Gestisci</a>
	</div>
	<div class="card overflow-hidden" id="categoriesList">
		<div class="empty-state"><span class="spinner-border spinner-border-sm"></span></div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>script/dashboard.js"></script>
<?php include __DIR__ . '/../components/footer.php'; ?>
