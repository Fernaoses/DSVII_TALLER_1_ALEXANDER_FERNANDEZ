<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/SavedBookRepository.php';

auth_session_guard();
$auth = new Auth();
$auth->requireAuth();

$book = [
    'id' => $_POST['book_id'] ?? '',
    'title' => $_POST['title'] ?? '',
    'author' => $_POST['author'] ?? '',
    'thumbnail' => $_POST['thumbnail'] ?? '',
    'review' => $_POST['review'] ?? ''
];

(new SavedBookRepository())->saveBook($book, $auth->currentUser()['id']);
header('Location: index.php');
exit;

function auth_session_guard(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}