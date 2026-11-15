<?php $activePage = $activePage ?? '';?>
<nav class="bottom-nav d-flex justify-content-around align-items-center text-center">
    <a href="<?= BASE_URL ?>dashboard" class="nav-item <?php if ($activePage === 'dashboard') echo 'active'; ?>">
        <i class="bi bi-pie-chart"></i>
        <small><?= te('nav.networth') ?></small>
    </a>
    <a href="<?= BASE_URL ?>statistics" class="nav-item <?php if ($activePage === 'statistics') echo 'active'; ?>">
        <i class="bi bi-graph-up"></i>
        <small><?= te('nav.history') ?></small>
    </a>
    <a href="<?= BASE_URL ?>settings" class="nav-item <?php if ($activePage === 'settings') echo 'active'; ?>">
        <i class="bi bi-gear"></i>
        <small><?= te('nav.settings') ?></small>
    </a>
</nav>
