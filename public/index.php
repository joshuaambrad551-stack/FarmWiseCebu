<?php

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::require();

$dashboard = new Dashboard($pdo);

render('dashboard', [
    'counts' => $dashboard->counts(),
    'byStatus' => $dashboard->plantingsByStatus(),
    'byCrop' => $dashboard->quantityByCrop(),
    'recentPlantings' => $dashboard->recentPlantings(),
    'recentHarvests' => $dashboard->recentHarvests(),
    'extraScripts' => ['assets/vendor/chart.umd.js', 'assets/dashboard.js'],
    'pageTitle' => 'Dashboard',
]);
