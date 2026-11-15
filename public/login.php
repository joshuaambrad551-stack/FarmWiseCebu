<?php

require_once __DIR__ . '/../includes/bootstrap.php';

if (Auth::check()) {
    redirect('index.php');
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } elseif (Auth::lockedSeconds() > 0) {
        $error = 'Too many failed attempts. Please wait ' . ceil(Auth::lockedSeconds() / 60) . ' minute(s) and try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($username === '' || $password === '') {
            $error = 'Please enter your username and password.';
        } elseif (Auth::attempt($pdo, $username, $password)) {
            redirect('index.php');
        } else {
            $error = Auth::lockedSeconds() > 0
                ? 'Too many failed attempts. Please wait 5 minutes and try again.'
                : 'Invalid username or password.';
        }
    }
}

if (isset($_GET['loggedout'])) {
    flash('success', 'You have been logged out.');
}

render('login', ['error' => $error, 'username' => $username, 'pageTitle' => 'Login']);
