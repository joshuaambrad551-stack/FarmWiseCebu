<?php
$current = basename($_SERVER['SCRIPT_NAME']);
$nav = [
    'index.php' => 'Dashboard',
    'farmers.php' => 'Farmers',
    'farms.php' => 'Farms',
    'crops.php' => 'Crops',
    'plantings.php' => 'Plantings',
    'harvests.php' => 'Harvests',
    'reports.php' => 'Reports',
];
if (Auth::isAdmin()) {
    $nav['users.php'] = 'Users';
}
$title = isset($pageTitle) ? $pageTitle . ' | FarmWise Cebu' : 'FarmWise Cebu';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link href="assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/logo.svg">
    <link href="assets/app.css" rel="stylesheet">
</head>
<body>
<?php if (Auth::check()): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top no-print">
    <div class="container-xl">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="assets/logo.svg" width="38" height="38" alt="">
            <span class="brand-text"><span class="brand-title">FarmWise Cebu</span><small>Smart Farm Records</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($nav as $file => $label): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $current === $file ? ' active' : '' ?>" href="<?= e($file) ?>"><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form method="post" action="logout.php" class="user-box d-flex align-items-center gap-2 ms-lg-auto mt-2 mt-lg-0">
                <?= csrf_field() ?>
                <span class="user-avatar" aria-hidden="true"><?= e(strtoupper(substr(Auth::user()['full_name'], 0, 1))) ?></span>
                <span class="user-info d-flex d-lg-none d-xl-flex flex-column">
                    <span class="user-name"><?= e(Auth::user()['full_name']) ?></span>
                    <small class="user-role"><?= e(Auth::user()['role_name']) ?></small>
                </span>
                <button type="submit" class="btn btn-sm btn-light logout-btn">Log out</button>
            </form>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container-xl py-4">
<?php foreach (flash_pull() as $m): ?>
    <div class="alert alert-<?= e($m['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($m['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>
