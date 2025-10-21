<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * @param array<string, mixed> $data
 *
 * @return array{nombre:?string,email:?string,password:?string}
 */
function validateUserData(array $data, bool $allowPartial = false): array
{
    $nombre = isset($data['nombre']) ? trim((string) $data['nombre']) : null;
    $email = isset($data['email']) ? filter_var((string) $data['email'], FILTER_SANITIZE_EMAIL) : null;
    $password = isset($data['password']) ? (string) $data['password'] : null;

    if (! $allowPartial) {
        if ($nombre === null || $nombre === '') {
            throw new InvalidArgumentException('El nombre es obligatorio.');
        }
        if ($email === null || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El email proporcionado no es válido.');
        }
        if ($password === null || strlen($password) < 6) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
        }
    }

    if ($nombre !== null && $nombre !== '' && mb_strlen($nombre) > 255) {
        throw new InvalidArgumentException('El nombre no puede exceder los 255 caracteres.');
    }
    if ($email !== null && $email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('El email proporcionado no es válido.');
    }
    if ($password !== null && $password !== '' && strlen($password) < 6) {
        throw new InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
    }

    return [
        'nombre' => $nombre,
        'email' => $email,
        'password' => $password,
    ];
}

function createUser(array $data): int
{
    $clean = validateUserData($data);
    $pdo = getPdoConnection();

    $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (:nombre, :email, :password)');
    $stmt->execute([
        'nombre' => $clean['nombre'],
        'email' => $clean['email'],
        'password' => password_hash((string) $clean['password'], PASSWORD_DEFAULT),
    ]);

    return (int) $pdo->lastInsertId();
}

function updateUser(int $id, array $data): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de usuario inválido.');
    }

    $clean = validateUserData($data, true);

    $fields = [];
    $params = ['id' => $id];

    if ($clean['nombre'] !== null && $clean['nombre'] !== '') {
        $fields[] = 'nombre = :nombre';
        $params['nombre'] = trim($clean['nombre']);
    }

    if ($clean['email'] !== null && $clean['email'] !== '') {
        $fields[] = 'email = :email';
        $params['email'] = $clean['email'];
    }

    if ($clean['password'] !== null && $clean['password'] !== '') {
        $fields[] = 'password_hash = :password';
        $params['password'] = password_hash($clean['password'], PASSWORD_DEFAULT);
    }

    if (! $fields) {
        throw new InvalidArgumentException('Debe proporcionar al menos un campo para actualizar.');
    }

    $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $pdo = getPdoConnection();
    $stmt = $pdo->prepare($sql);

    return $stmt->execute($params);
}

function deleteUser(int $id): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de usuario inválido.');
    }

    $pdo = getPdoConnection();
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');

    return $stmt->execute(['id' => $id]);
}

/**
 * @return array{data:list<array<string,mixed>>,total:int,page:int,per_page:int}
 */
function listUsers(int $page, int $perPage, ?string $search = null): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $pdo = getPdoConnection();
    $conditions = [];
    $params = [];

    if ($search !== null && $search !== '') {
        $term = '%' . trim($search) . '%';
        $conditions[] = '(nombre LIKE :search OR email LIKE :search)';
        $params['search'] = $term;
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios' . $where);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $listStmt = $pdo->prepare('SELECT id, nombre, email, creado_en FROM usuarios' . $where . ' ORDER BY nombre ASC LIMIT :limit OFFSET :offset');
    foreach ($params as $key => $value) {
        $listStmt->bindValue(':' . $key, $value);
    }
    $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();
    $rows = $listStmt->fetchAll();

    return [
        'data' => $rows,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
    ];
}

function getUserById(int $id): ?array
{
    $pdo = getPdoConnection();
    $stmt = $pdo->prepare('SELECT id, nombre, email, creado_en FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}