<?php
$page = $data['page'];
$pages = $data['pages'];
$total = $data['total'];
$from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$to = min($total, $page * $perPage);
$isAdmin = Auth::isAdmin();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0"><?= e($cfg['title']) ?></h1>
    <a href="<?= e($cfg['page']) ?>?action=create" class="btn btn-success">Add <?= e($cfg['singular']) ?></a>
</div>

<form method="get" action="<?= e($cfg['page']) ?>" class="card card-body mb-3 no-print">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-6 col-lg-4">
            <label class="form-label small mb-1" for="q">Search</label>
            <input type="search" class="form-control" id="q" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e($cfg['search_placeholder']) ?>">
        </div>
        <?php foreach ($cfg['filters'] as $f): ?>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="f_<?= e($f['name']) ?>"><?= e($f['label']) ?></label>
                <?php if ($f['type'] === 'select'): ?>
                    <select class="form-select" id="f_<?= e($f['name']) ?>" name="<?= e($f['name']) ?>">
                        <option value="">All</option>
                        <?php foreach ($lookup->options($f['lookup']) as $optId => $optLabel): ?>
                            <option value="<?= e($optId) ?>"<?= ((string) ($_GET[$f['name']] ?? '') === (string) $optId) ? ' selected' : '' ?>><?= e($optLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="<?= e($f['type']) ?>" class="form-control" id="f_<?= e($f['name']) ?>" name="<?= e($f['name']) ?>" value="<?= e($_GET[$f['name']] ?? '') ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="col-12 col-lg-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary">Apply</button>
            <a href="<?= e($cfg['page']) ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <?php foreach ($cfg['columns'] as $col): ?>
                        <th scope="col" class="text-nowrap">
                            <?php if (!empty($col['sort'])):
                                $nextDir = ($data['sort'] === $col['sort'] && $data['dir'] === 'asc') ? 'desc' : 'asc';
                                $arrow = $data['sort'] === $col['sort'] ? ($data['dir'] === 'asc' ? ' &#9650;' : ' &#9660;') : '';
                                ?>
                                <a class="text-decoration-none text-dark" href="?<?= e(query_string(['sort' => $col['sort'], 'dir' => $nextDir, 'page' => 1])) ?>"><?= e($col['label']) ?><?= $arrow ?></a>
                            <?php else: ?>
                                <?= e($col['label']) ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    <th scope="col" class="text-end text-nowrap no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$data['rows']): ?>
                    <tr><td colspan="<?= count($cfg['columns']) + 1 ?>" class="text-center text-muted py-4">No records found.</td></tr>
                <?php endif; ?>
                <?php foreach ($data['rows'] as $row): ?>
                    <tr>
                        <?php foreach ($cfg['columns'] as $col): ?>
                            <td class="<?= in_array($col['format'] ?? 'text', ['text'], true) ? '' : 'text-nowrap' ?>"><?= format_value($row[$col['key']], $col['format'] ?? 'text') ?></td>
                        <?php endforeach; ?>
                        <td class="text-end text-nowrap no-print">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e($cfg['page']) ?>?action=view&amp;id=<?= (int) $row[$cfg['pk']] ?>">View</a>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e($cfg['page']) ?>?action=edit&amp;id=<?= (int) $row[$cfg['pk']] ?>">Edit</a>
                            <?php if ($isAdmin): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-url="<?= e($cfg['page']) ?>?action=delete&amp;id=<?= (int) $row[$cfg['pk']] ?>"
                                        data-name="<?= e(($cfg['delete_prefix'] ?? '') . $row[$cfg['delete_label']]) ?>">Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="small text-muted">Showing <?= $from ?>-<?= $to ?> of <?= $total ?> record(s)</span>
        <?php if ($pages > 1):
            $start = max(1, $page - 2);
            $end = min($pages, $page + 2);
            ?>
            <nav aria-label="Pagination" class="no-print">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="?<?= e(query_string(['page' => $page - 1])) ?>">Previous</a></li>
                    <?php if ($start > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?= e(query_string(['page' => 1])) ?>">1</a></li>
                        <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item<?= $i === $page ? ' active' : '' ?>"><a class="page-link" href="?<?= e(query_string(['page' => $i])) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($end < $pages): ?>
                        <?php if ($end < $pages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="?<?= e(query_string(['page' => $pages])) ?>"><?= $pages ?></a></li>
                    <?php endif; ?>
                    <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>"><a class="page-link" href="?<?= e(query_string(['page' => $page + 1])) ?>">Next</a></li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
