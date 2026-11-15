<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Reports</h1>
    <button type="button" class="btn btn-outline-secondary no-print" onclick="window.print()">Print report</button>
</div>

<ul class="nav nav-pills mb-3 no-print">
    <?php foreach ($types as $key => $label): ?>
        <li class="nav-item">
            <a class="nav-link<?= $key === $type ? ' active' : '' ?>" href="reports.php?type=<?= e($key) ?>"><?= e($label) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<form method="get" action="reports.php" class="card card-body mb-3 no-print">
    <input type="hidden" name="type" value="<?= e($type) ?>">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1" for="date_from"><?= $type === 'farmers_farms' ? 'Registered from' : ($type === 'harvests' || $type === 'harvest_total' || $type === 'harvest_value' ? 'Harvested from' : 'Planted from') ?></label>
            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($_GET['date_from'] ?? '') ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1" for="date_to">To</label>
            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($_GET['date_to'] ?? '') ?>">
        </div>
        <?php if ($type === 'planting_status'): ?>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1" for="status_id">Status</label>
                <select class="form-select" id="status_id" name="status_id">
                    <option value="">All</option>
                    <?php foreach ($lookup->options('planting_status') as $optId => $optLabel): ?>
                        <option value="<?= e($optId) ?>"<?= (string) ($_GET['status_id'] ?? '') === (string) $optId ? ' selected' : '' ?>><?= e($optLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1" for="farm_id">Farm</label>
                <select class="form-select" id="farm_id" name="farm_id">
                    <option value="">All farms</option>
                    <?php foreach ($lookup->options('farm') as $optId => $optLabel): ?>
                        <option value="<?= e($optId) ?>"<?= (string) ($_GET['farm_id'] ?? '') === (string) $optId ? ' selected' : '' ?>><?= e($optLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-12 col-md-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary">Generate</button>
            <a href="reports.php?type=<?= e($type) ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header"><?= e($types[$type]) ?> <span class="text-muted small">&middot; generated <?= e(date('Y-m-d H:i')) ?></span></div>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <?php foreach ($report['columns'] as [$key, $label, $format]): ?>
                        <th scope="col" class="text-nowrap"><?= e($label) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$report['rows']): ?>
                    <tr><td colspan="<?= count($report['columns']) ?>" class="text-center text-muted py-4">No records found for the selected filters.</td></tr>
                <?php endif; ?>
                <?php foreach ($report['rows'] as $row): ?>
                    <tr>
                        <?php foreach ($report['columns'] as [$key, $label, $format]): ?>
                            <td class="<?= $format === 'text' ? '' : 'text-nowrap' ?>"><?= format_value($row[$key], $format) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($report['rows'] && $report['totals']): ?>
                <tfoot class="table-group-divider">
                    <tr class="fw-semibold">
                        <?php foreach ($report['columns'] as $i => [$key, $label, $format]): ?>
                            <td>
                                <?php if ($i === 0): ?>
                                    Total
                                <?php elseif (isset($report['totals'][$key])): ?>
                                    <?= format_value($report['totals'][$key], $format === 'text' ? 'number' : $format) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
    <div class="card-footer small text-muted"><?= count($report['rows']) ?> row(s)</div>
</div>
