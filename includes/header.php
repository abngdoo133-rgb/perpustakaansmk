<?php
require_once __DIR__ . '/auth.php';
$page_title = $page_title ?? 'Perpustakaan SMK';
$base = $base ?? '';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?> - Perpustakaan SMK</title>
<link rel="stylesheet" href="<?= $base ?>assets/css/app.css">
</head>
<body>
<div class="app-shell">
