<?php
require_once __DIR__ . '/../src/Auth.php';

auth_session_guard();
$auth = new Auth();

try {
    $auth->handleGoogleCallback();
    header('Location: index.php');
    exit;
} catch (Throwable $e) {
    echo 'Error en autenticación: ' . htmlspecialchars($e->getMessage());
}

function auth_session_guard(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}