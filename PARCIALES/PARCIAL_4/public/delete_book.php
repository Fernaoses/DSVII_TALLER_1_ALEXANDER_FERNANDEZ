<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/SavedBookRepository.php';

auth_session_guard();
$auth = new Auth();
$auth->requireAuth();

$bookId = (int) ($_POST['id'] ?? 0);
if ($bookId > 0) {
    (new SavedBookRepository())->deleteBook($bookId, $auth->currentUser()['id']);
}

header('Location: index.php');
exit;

function auth_session_guard(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}