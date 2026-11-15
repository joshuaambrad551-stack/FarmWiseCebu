<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$module = new ModuleController(require __DIR__ . '/../modules/farms.php', $pdo, $lookup);
$module->run();
