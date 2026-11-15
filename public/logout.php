<?php

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    Auth::logout();
    redirect('login.php?loggedout=1');
}

redirect(Auth::check() ? 'index.php' : 'login.php');
