<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h3 mb-0"><?= e($cfg['singular']) ?> #<?= (int) $record[$cfg['pk']] ?></h1>
            <div class="d-flex gap-2">
                <a href="<?= e($cfg['page']) ?>?action=edit&amp;id=<?= (int) $record[$cfg['pk']] ?>" class="btn btn-primary">Edit</a>
                <a href="<?= e($cfg['page']) ?>" class="btn btn-outline-secondary">Back to list</a>
            </div>
        </div>
        <div class="card">
            <dl class="row mb-0 p-3">
                <?php foreach ($cfg['view'] as $v): ?>
                    <dt class="col-sm-5"><?= e($v['label']) ?></dt>
                    <dd class="col-sm-7"><?= format_value($record[$v['key']], $v['format'] ?? 'text') ?></dd>
                <?php endforeach; ?>
            </dl>
            <?php if (array_key_exists('created_at', $record)): ?>
                <div class="card-footer small text-muted">
                    Created <?= e(format_value($record['created_at'], 'datetime')) ?><?= !empty($record['created_by_name']) ? ' by ' . e($record['created_by_name']) : '' ?>
                    &middot;
                    Last updated <?= e(format_value($record['updated_at'], 'datetime')) ?><?= !empty($record['updated_by_name']) ? ' by ' . e($record['updated_by_name']) : '' ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
