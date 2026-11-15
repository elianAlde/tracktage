<?php
	require_once __DIR__ . '/../middleware/auth.php';
	include __DIR__ . '/../components/head.php';

	$topBarTitle = 'Impostazioni';
	$activePage = 'settings';
	include __DIR__ . '/../components/menu-top.php';

	$icons = [
		'bi-graph-up-arrow' => 'Grafico', 'bi-currency-bitcoin' => 'Crypto', 'bi-wallet2' => 'Portafoglio',
		'bi-bank' => 'Banca', 'bi-piggy-bank' => 'Salvadanaio', 'bi-shield-check' => 'Scudo',
		'bi-car-front' => 'Auto', 'bi-bicycle' => 'Moto/bici', 'bi-house' => 'Casa', 'bi-building' => 'Edificio',
		'bi-gem' => 'Gioielli', 'bi-coin' => 'Moneta', 'bi-briefcase' => 'Lavoro', 'bi-credit-card' => 'Carta',
		'bi-cash-stack' => 'Contanti', 'bi-umbrella' => 'Previdenza', 'bi-box-seam' => 'Oggetti', 'bi-laptop' => 'Elettronica',
	];
	$colors = ['#0d6efd', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#f7931a', '#ffc107', '#198754', '#20c997', '#0dcaf0', '#6c757d', '#212529'];
?>

<div class="container app-container py-3">
	<!-- PROFILO -->
	<div class="card p-3 mb-3 d-flex flex-row align-items-center gap-3">
		<div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
			<i class="bi bi-person fs-3 text-secondary"></i>
		</div>
		<div class="min-w-0">
			<div class="fw-semibold"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></div>
			<div class="text-muted small text-truncate"><?= htmlspecialchars($_SESSION['email']) ?></div>
		</div>
	</div>

	<!-- SEZIONI -->
	<div class="d-flex justify-content-between align-items-center mb-2 px-1" id="sections">
		<h6 class="fw-semibold mb-0">Sezioni del patrimonio</h6>
		<a href="#" class="small text-decoration-none" id="btnAddCategory"><i class="bi bi-plus-lg" style="font-size:14px"></i> Nuova</a>
	</div>
	<div class="card overflow-hidden mb-3" id="categoriesList"></div>

	<!-- ACCOUNT -->
	<div class="card overflow-hidden mb-3">
		<a href="#" class="list-row" data-bs-toggle="modal" data-bs-target="#modalPassword">
			<i class="bi bi-key" style="font-size:20px"></i><span class="flex-grow-1">Cambia password</span><i class="bi bi-chevron-right text-muted" style="font-size:14px"></i>
		</a>
		<a href="#" class="list-row" id="addToHomeBtn">
			<i class="bi bi-plus-square" style="font-size:20px"></i><span class="flex-grow-1">Aggiungi a schermata home</span><i class="bi bi-chevron-right text-muted" style="font-size:14px"></i>
		</a>
	</div>

	<!-- LOGOUT -->
	<div class="d-grid mt-4">
		<a href="<?= BASE_URL ?>logout" class="btn btn-outline-danger d-flex justify-content-center align-items-center">
			<i class="bi bi-box-arrow-right me-2" style="font-size:18px"></i> Logout
		</a>
	</div>
</div>

<!-- MODAL SEZIONE -->
<div class="modal fade" id="modalCategory" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalCategoryTitle">Nuova sezione</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form id="formCategory" novalidate>
					<input type="hidden" name="id" id="catId">
					<input type="hidden" name="color" id="catColor" value="<?= $colors[0] ?>">
					<div class="mb-3">
						<label class="form-label" for="catName">Nome</label>
						<input type="text" class="form-control" id="catName" name="name" maxlength="60" placeholder="es. Fondo pensione" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Tipo di valore</label>
						<select class="form-select" name="kind" id="catKind">
							<option value="manual">Inserito a mano (liquidità, veicoli, immobili…)</option>
							<option value="market">Quotazione di mercato (ETF, azioni, crypto)</option>
						</select>
						<div class="form-text" id="kindWarning"></div>
					</div>
					<div class="mb-3">
						<label class="form-label" for="catIcon">Icona</label>
						<select class="form-select" name="icon" id="catIcon">
							<?php foreach ($icons as $cls => $label): ?>
								<option value="<?= $cls ?>"><?= $label ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Colore</label>
						<div class="d-flex flex-wrap gap-2" id="colorPicker">
							<?php foreach ($colors as $c): ?>
								<span class="color-swatch" data-color="<?= $c ?>" style="background:<?= $c ?>"></span>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" id="catLiability" name="is_liability" value="1">
						<label class="form-check-label" for="catLiability">È una passività (debiti, mutuo) – viene sottratta</label>
					</div>
				</form>
			</div>
			<div class="modal-footer d-flex justify-content-between">
				<button type="button" class="btn btn-outline-danger d-none" id="btnDeleteCategory">Elimina</button>
				<button type="submit" class="btn btn-primary ms-auto" form="formCategory">Salva</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL PASSWORD -->
<div class="modal fade" id="modalPassword" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cambia password</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form id="formPassword" novalidate>
					<input type="email" class="d-none" autocomplete="username" value="<?= htmlspecialchars($_SESSION['email']) ?>">
					<div class="mb-3">
						<label class="form-label" for="current_password">Password attuale</label>
						<input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
					</div>
					<div class="mb-3">
						<label class="form-label" for="new_password">Nuova password</label>
						<input type="password" class="form-control" id="new_password" name="new_password" minlength="10" autocomplete="new-password" required>
						<div class="form-text">Almeno 10 caratteri.</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary" form="formPassword">Aggiorna</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL PER TUTORIAL INSTALLAZIONE IN HOME IOS -->
<div class="modal fade" id="iosInstallModal" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content rounded-4">
			<div class="modal-header">
				<h5 class="modal-title">Installa Tracktage</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<p class="mb-3">Per aggiungere la webapp alla schermata Home del tuo iPhone:</p>
				<ol class="ps-3">
					<li class="mb-2">Tocca <strong>Condividi</strong> <i class="bi bi-box-arrow-up fs-5 mx-1"></i></li>
					<li class="mb-2">Scorri e seleziona <strong>Aggiungi a Home</strong> <i class="bi bi-plus-square fs-5 mx-1"></i></li>
					<li>Premi <strong>Aggiungi</strong></li>
				</ol>
				<div class="alert alert-light mt-3 small">Funziona solo da Safari</div>
			</div>
		</div>
	</div>
</div>

<script src="<?= BASE_URL ?>script/settings.js"></script>
<?php include __DIR__ . '/../components/footer.php'; ?>
