<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$module = new ModuleController(require __DIR__ . '/../modules/users.php', $pdo, $lookup);
$module->run();
