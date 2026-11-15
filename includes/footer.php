</main>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" id="deleteForm" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to permanently delete <strong id="deleteName"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
<script src="assets/vendor/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
<?php if (!empty($extraScripts)): foreach ($extraScripts as $src): ?>
<script src="<?= e($src) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
