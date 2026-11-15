<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <div class="text-center mb-4">
            <img src="assets/logo.svg" width="72" height="72" alt="FarmWise Cebu logo" class="mb-2">
            <h1 class="h3">FarmWise Cebu</h1>
            <p class="text-muted mb-0">Smart Farm Records &middot; Please log in to continue</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php" class="card card-body" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= e($username) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Log in</button>
        </form>
    </div>
</div>
