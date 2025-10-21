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

    $passwordHash = password_hash((string) $clean['password'], PASSWORD_DEFAULT);

    $connection = getMysqliConnection();
    $stmt = $connection->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $clean['nombre'], $clean['email'], $passwordHash);
    $stmt->execute();

    return (int) $connection->insert_id;
}

function updateUser(int $id, array $data): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de usuario inválido.');
    }

    $clean = validateUserData($data, true);

    $fields = [];
    $params = [];
    $types = '';

    if ($clean['nombre'] !== null && $clean['nombre'] !== '') {
        $fields[] = 'nombre = ?';
        $params[] = trim($clean['nombre']);
        $types .= 's';
    }

    if ($clean['email'] !== null && $clean['email'] !== '') {
        $fields[] = 'email = ?';
        $params[] = $clean['email'];
        $types .= 's';
    }

    if ($clean['password'] !== null && $clean['password'] !== '') {
        $fields[] = 'password_hash = ?';
        $params[] = password_hash($clean['password'], PASSWORD_DEFAULT);
        $types .= 's';
    }

    if (! $fields) {
        throw new InvalidArgumentException('Debe proporcionar al menos un campo para actualizar.');
    }

    $params[] = $id;
    $types .= 'i';

    $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $connection = getMysqliConnection();
    $stmt = $connection->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

function deleteUser(int $id): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de usuario inválido.');
    }

    $connection = getMysqliConnection();
    $stmt = $connection->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

/**
 * @return array{data:list<array<string,mixed>>,total:int,page:int,per_page:int}
 */
function listUsers(int $page, int $perPage, ?string $search = null): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $connection = getMysqliConnection();
    $conditions = [];
    $params = [];
    $types = '';

    if ($search !== null && $search !== '') {
        $term = '%' . trim($search) . '%';
        $conditions[] = '(nombre LIKE ? OR email LIKE ?)';
        $params[] = $term;
        $params[] = $term;
        $types .= 'ss';
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

    $countSql = 'SELECT COUNT(*) FROM usuarios' . $where;
    $countStmt = $connection->prepare($countSql);
    if ($types !== '') {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);

    $sql = 'SELECT id, nombre, email, creado_en FROM usuarios' . $where . ' ORDER BY nombre ASC LIMIT ? OFFSET ?';
    $typesWithLimit = $types . 'ii';
    $paramsWithLimit = $params;
    $paramsWithLimit[] = $perPage;
    $paramsWithLimit[] = $offset;

    $stmt = $connection->prepare($sql);
    $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return [
        'data' => $rows,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
    ];
}

function getUserById(int $id): ?array
{
    $connection = getMysqliConnection();
    $stmt = $connection->prepare('SELECT id, nombre, email, creado_en FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc() ?: null;
}