<?php
require_once __DIR__ . '/../src/Auth.php';

auth_session_guard();
$auth = new Auth();
$auth->logout();
header('Location: index.php');
exit;

function auth_session_guard(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}