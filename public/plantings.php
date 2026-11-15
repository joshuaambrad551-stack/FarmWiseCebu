<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$module = new ModuleController(require __DIR__ . '/../modules/plantings.php', $pdo, $lookup);
$module->run();
