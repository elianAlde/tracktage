<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../services/Portfolio.php';
requireAuth();

Response::success((new Portfolio())->summary(input('refresh') === '1'));
