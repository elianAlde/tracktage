<?php
	require_once __DIR__ . '/../config/config.php';

	if (isset($_SESSION['id'])) {
		header("Location: " . BASE_URL . "dashboard");
		exit;
	}

	include __DIR__ . '/../components/head.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/login.css">

<div class="login-container">
	<div class="text-center mb-4">
		<div class="login-logo mx-auto"><i class="bi bi-safe2"></i></div>
		<h3 class="fw-bold mt-3">Tracktage</h3>
		<p class="text-muted">Accedi per vedere il tuo patrimonio</p>
	</div>

	<form id="formLogin" class="needs-validation" novalidate method="post">
		<div class="mb-3">
			<input type="email" class="form-control" placeholder="Email" id="email" name="email" autocomplete="username" required>
			<div class="invalid-feedback">Inserisci un'email valida.</div>
		</div>

		<div class="mb-3">
			<input type="password" class="form-control" placeholder="Password" id="password" name="password" autocomplete="current-password" required>
			<div class="invalid-feedback">La password è obbligatoria.</div>
		</div>

		<div id="loginError" class="alert alert-danger py-2 small d-none"></div>

		<div class="d-grid mb-3">
			<button class="btn btn-primary" type="submit">Accedi</button>
		</div>
	</form>
</div>

<script src="<?= BASE_URL ?>script/login.js"></script>

<?php
	$hideBottomMenu = true;
	include __DIR__ . '/../components/footer.php';
?>
