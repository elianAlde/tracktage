<?php
	$topBarTitle = $topBarTitle ?? 'Tracktage';
	$topBarBack = $topBarBack ?? false;
	$topBarAdd = $topBarAdd ?? false;
	$topBarRefresh = $topBarRefresh ?? false;
?>

<div class="top-bar d-flex justify-content-between align-items-center px-3">
	<!-- Sinistra -->
	<div class="top-bar-side">
		<?php if ($topBarBack): ?>
			<a href="<?= is_string($topBarBack) ? $topBarBack : 'javascript:history.back()' ?>" class="text-body" aria-label="Indietro">
				<i class="bi bi-arrow-left"></i>
			</a>
		<?php elseif ($topBarRefresh): ?>
			<a href="#" class="text-body" id="btnRefresh" aria-label="Aggiorna quotazioni">
				<i class="bi bi-arrow-clockwise"></i>
			</a>
		<?php endif; ?>
	</div>

	<!-- Centro -->
	<div class="text-center flex-grow-1 text-truncate">
		<span class="fw-semibold" id="topBarTitle"><?= htmlspecialchars($topBarTitle) ?></span>
	</div>

	<!-- Destra -->
	<div class="top-bar-side text-end">
		<?php if ($topBarAdd): ?>
			<a href="#" class="text-body" id="btnAdd" aria-label="Aggiungi">
				<i class="bi bi-plus-lg"></i>
			</a>
		<?php endif; ?>
	</div>
</div>
