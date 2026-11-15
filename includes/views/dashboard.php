<?php
$cards = [
    ['Farmers', $counts['farmers'], 'farmers.php', 'success'],
    ['Farms', $counts['farms'], 'farms.php', 'primary'],
    ['Crops', $counts['crops'], 'crops.php', 'warning'],
    ['Planting Records', $counts['plantings'], 'plantings.php', 'info'],
    ['Harvest Records', $counts['harvests'], 'harvests.php', 'danger'],
];
?>
<h1 class="h3 mb-3">Dashboard</h1>

<div class="row g-3 mb-3">
    <?php foreach ($cards as [$label, $value, $link, $color]): ?>
        <div class="col-6 col-md-4 col-xl">
            <a href="<?= e($link) ?>" class="text-decoration-none">
                <div class="card stat-card border-<?= e($color) ?> h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase"><?= e($label) ?></div>
                        <div class="display-6 fw-semibold text-dark"><?= e(format_value($value, 'int')) ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6">
        <div class="card h-100 sum-card sum-C"><div class="card-body">
            <div>
                <div class="sum-label">Total harvested quantity</div>
                <div class="sum-value"><?= e(format_value($counts['total_quantity'], 'number')) ?> <span class="sum-unit">kg</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card h-100 sum-card sum-C sum-money"><div class="card-body">
            <div>
                <div class="sum-label">Estimated harvest value</div>
                <div class="sum-value"><?= e(format_value($counts['total_value'], 'money')) ?></div>
            </div>
        </div></div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header">Plantings by status</div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="statusChart"
                            data-labels="<?= e(json_encode(array_column($byStatus, 'status_name'))) ?>"
                            data-values="<?= e(json_encode(array_map('intval', array_column($byStatus, 'total')))) ?>"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header">Harvested quantity by crop (kg)</div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="cropChart"
                            data-labels="<?= e(json_encode(array_column($byCrop, 'crop_name'))) ?>"
                            data-values="<?= e(json_encode(array_map('floatval', array_column($byCrop, 'quantity')))) ?>"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"><span>Recent planting records</span><a href="plantings.php" class="small">View all</a></div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Farm</th><th>Crop</th><th>Planted</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentPlantings as $r): ?>
                        <tr>
                            <td><?= e($r['farm_name']) ?></td>
                            <td><?= e($r['crop_name']) ?></td>
                            <td><?= e($r['planting_date']) ?></td>
                            <td><?= format_value($r['status_name'], 'status') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"><span>Recent harvest records</span><a href="harvests.php" class="small">View all</a></div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Farm</th><th>Crop</th><th>Harvested</th><th class="text-end">Qty (kg)</th><th>Grade</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentHarvests as $r): ?>
                        <tr>
                            <td><?= e($r['farm_name']) ?></td>
                            <td><?= e($r['crop_name']) ?></td>
                            <td><?= e($r['harvest_date']) ?></td>
                            <td class="text-end"><?= e(format_value($r['quantity_kg'], 'number')) ?></td>
                            <td><?= e($r['grade_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
