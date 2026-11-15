<?php

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::require();

$types = Report::types();
$type = $_GET['type'] ?? 'farmers_farms';
if (!isset($types[$type])) {
    $type = 'farmers_farms';
}

$report = (new Report($pdo))->run($type, $_GET);

render('reports', [
    'types' => $types,
    'type' => $type,
    'report' => $report,
    'lookup' => $lookup,
    'pageTitle' => 'Reports',
]);
