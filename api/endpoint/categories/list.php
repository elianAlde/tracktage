<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requireAuth();

Response::success(Connection::get()->query(
    "SELECT c.*, (SELECT COUNT(*) FROM assets a WHERE a.category_id = c.id) AS count
     FROM categories c ORDER BY c.sort_order, c.id"
)->fetchAll());
