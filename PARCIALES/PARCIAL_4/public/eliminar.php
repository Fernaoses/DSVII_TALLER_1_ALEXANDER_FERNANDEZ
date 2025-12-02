<?php
require_once __DIR__ . '/../database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo 'ID de producto inválido.';
    exit;
}

$pdo = get_connection();
$stmt = $pdo->prepare('DELETE FROM productos WHERE id = :id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

header('Location: index.php');
exit;