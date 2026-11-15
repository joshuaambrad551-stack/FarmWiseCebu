<?php
$action = $cfg['page'] . '?action=' . ($id === null ? 'create' : 'edit&id=' . $id);
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <h1 class="h3 mb-3"><?= $id === null ? 'Add' : 'Edit' ?> <?= e($cfg['singular']) ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Please fix the following:</strong>
                <ul class="mb-0">
                    <?php foreach ($errors as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e($action) ?>" class="card card-body" novalidate>
            <?= csrf_field() ?>
            <?php foreach ($fields as $name => $f):
                $value = $input[$name] ?? '';
                $invalid = isset($errors[$name]);
                $class = ($f['type'] === 'select' ? 'form-select' : 'form-control') . ($invalid ? ' is-invalid' : '');
                ?>
                <div class="mb-3">
                    <?php if ($f['type'] === 'checkbox'): ?>
                        <div class="form-check">
                            <input class="form-check-input<?= $invalid ? ' is-invalid' : '' ?>" type="checkbox" id="<?= e($name) ?>" name="<?= e($name) ?>" value="1"<?= $value === '1' ? ' checked' : '' ?>>
                            <label class="form-check-label" for="<?= e($name) ?>"><?= e($f['label']) ?></label>
                            <?php if ($invalid): ?><div class="invalid-feedback"><?= e($errors[$name]) ?></div><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php
                        $required = $f['required'] ?? true;
                        if ($required === 'create') {
                            $required = $id === null;
                        }
                        ?>
                        <label class="form-label" for="<?= e($name) ?>"><?= e($f['label']) ?><?= $required ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <?php if ($f['type'] === 'select'): ?>
                            <select class="<?= $class ?>" id="<?= e($name) ?>" name="<?= e($name) ?>">
                                <option value="">-- Select <?= e(strtolower($f['label'])) ?> --</option>
                                <?php foreach ($lookup->options($f['lookup']) as $optId => $optLabel): ?>
                                    <option value="<?= e($optId) ?>"<?= (string) $value === (string) $optId ? ' selected' : '' ?>><?= e($optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($f['type'] === 'number'): ?>
                            <input type="number" class="<?= $class ?>" id="<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>"
                                   step="<?= e($f['step'] ?? '1') ?>" min="<?= e($f['min'] ?? 0) ?>">
                        <?php elseif ($f['type'] === 'date'): ?>
                            <input type="date" class="<?= $class ?>" id="<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>">
                        <?php elseif ($f['type'] === 'password'): ?>
                            <input type="password" class="<?= $class ?>" id="<?= e($name) ?>" name="<?= e($name) ?>" autocomplete="new-password">
                            <?php if ($id !== null): ?><div class="form-text">Leave blank to keep the current password.</div><?php endif; ?>
                        <?php else: ?>
                            <input type="<?= $f['type'] === 'tel' ? 'tel' : 'text' ?>" class="<?= $class ?>" id="<?= e($name) ?>" name="<?= e($name) ?>"
                                   value="<?= e($value) ?>" maxlength="<?= e($f['max'] ?? 255) ?>">
                        <?php endif; ?>
                        <?php if ($invalid): ?><div class="invalid-feedback d-block"><?= e($errors[$name]) ?></div><?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Save</button>
                <a href="<?= e($cfg['page']) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
