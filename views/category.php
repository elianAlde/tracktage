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
				<div class="text-muted small"><?= $category['is_liability'] ? 'Totale passività' : 'Valore totale' ?></div>
				<div class="fs-3 fw-bold" id="categoryTotal">—</div>
				<?php if ($isMarket): ?>
					<div class="small" id="categoryGain"></div>
				<?php endif; ?>
			</div>
			<?php if ($isMarket): ?>
				<button class="btn btn-light btn-sm" id="btnRefresh" title="Aggiorna quotazioni"><i class="bi bi-arrow-clockwise" style="font-size:18px"></i></button>
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

<!-- Modal aggiunta / modifica voce -->
<div class="modal fade" id="modalAsset" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalAssetTitle">Aggiungi</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
			</div>
			<div class="modal-body">
				<form id="formAsset" novalidate autocomplete="off">
					<input type="hidden" name="id" id="assetId">
					<input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">

					<?php if ($isMarket): ?>
						<div class="mb-3 position-relative">
							<label for="ticker" class="form-label">Ticker, ISIN o nome</label>
							<input type="text" class="form-control text-uppercase" id="ticker" name="ticker" placeholder="es. VWCE.DE, SWDA.MI, IE00BK5BQT80" required>
							<div class="list-group shadow search-results d-none" id="searchResults"></div>
							<div class="form-text" id="tickerInfo">Cerca e seleziona lo strumento sulla borsa in cui lo hai comprato.</div>
						</div>
						<div class="row g-2">
							<div class="col-6 mb-3">
								<label for="quantity" class="form-label">Quote</label>
								<input type="text" inputmode="decimal" class="form-control" id="quantity" name="quantity" placeholder="0" required>
							</div>
							<div class="col-6 mb-3">
								<label for="avg_cost" class="form-label">Prezzo medio <span class="text-muted small">(opz.)</span></label>
								<input type="text" inputmode="decimal" class="form-control" id="avg_cost" name="avg_cost" placeholder="<?= BASE_CURRENCY ?> per quota">
							</div>
						</div>
						<div class="alert alert-light small py-2 d-none" id="previewValue"></div>
						<div class="mb-3">
							<label for="name" class="form-label">Nome <span class="text-muted small">(opz.)</span></label>
							<input type="text" class="form-control" id="name" name="name" maxlength="120" placeholder="Compilato in automatico">
						</div>
					<?php else: ?>
						<div class="mb-3">
							<label for="name" class="form-label">Nome</label>
							<input type="text" class="form-control" id="name" name="name" maxlength="120" placeholder="es. Conto corrente, Fiat Panda 2019" required>
						</div>
						<div class="mb-3">
							<label for="value" class="form-label"><?= $category['is_liability'] ? 'Debito residuo' : 'Valore' ?> (<?= BASE_CURRENCY ?>)</label>
							<input type="text" inputmode="decimal" class="form-control" id="value" name="value" placeholder="0,00" required>
						</div>
					<?php endif; ?>

					<div class="mb-1">
						<label for="notes" class="form-label">Note <span class="text-muted small">(opz.)</span></label>
						<textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer d-flex justify-content-between">
				<button type="button" class="btn btn-outline-danger d-none" id="btnDelete"><i class="bi bi-trash" style="font-size:16px"></i> Elimina</button>
				<button type="submit" class="btn btn-primary ms-auto" form="formAsset">Salva</button>
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
