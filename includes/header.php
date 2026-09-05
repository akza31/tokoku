<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
$u = current_user();
$__settings = db()->query('SELECT store_name FROM settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$__storeName = htmlspecialchars($__settings['store_name'] ?? APP_NAME);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title><?= $__storeName ?></title>
    <link rel="icon" type="image/png" href="assets/icon.png">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; padding-bottom: 70px; }
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #dee2e6; z-index: 1030; display: flex; justify-content: space-around; padding: 10px 0; }
        .nav-item { text-align: center; color: #6c757d; text-decoration: none; font-size: 0.8rem; }
        .nav-item i { display: block; font-size: 1.5rem; margin-bottom: 2px; }
        .nav-item.active { color: #0d6efd; }
        .top-bar { background: #0d6efd; color: #fff; padding: 15px; position: sticky; top: 0; z-index: 1020; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="top-bar d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold"><?= $__storeName ?></h5>
        <div>
            <span class="badge bg-light text-primary"><?= htmlspecialchars($u['name'] ?? '') ?></span>
        </div>
    </div>
    <div class="container mt-3">
