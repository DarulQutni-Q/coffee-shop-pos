<?php
require_once __DIR__ . '/../../config/auth.php';
requireLogin();

// Redirect kasir to POS
if (isKasir()) {
    header('Location: /kasir/pos');
    exit;
}
?>
