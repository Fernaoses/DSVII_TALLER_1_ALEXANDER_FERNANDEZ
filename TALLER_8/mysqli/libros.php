<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Valida y sanitiza la información básica de un libro.
 *
 * @param array<string, mixed> $data
 * @param bool $allowPartial Permite que los campos estén ausentes para actualizaciones parciales.
 *
 * @return array{titulo:?string,autor:?string,isbn:?string,anio:?int,cantidad:?int}
 */
function validateBookData(array $data, bool $allowPartial = false): array
{
    $titulo = isset($data['titulo']) ? trim((string) $data['titulo']) : null;
    $autor = isset($data['autor']) ? trim((string) $data['autor']) : null;
    $isbn = isset($data['isbn']) ? preg_replace('/[^0-9A-Za-z\-]/', '', (string) $data['isbn']) : null;
    $anio = isset($data['anio']) ? filter_var($data['anio'], FILTER_VALIDATE_INT) : null;
    $cantidad = isset($data['cantidad']) ? filter_var($data['cantidad'], FILTER_VALIDATE_INT) : null;

    if (! $allowPartial) {
        if ($titulo === null || $titulo === '') {
            throw new InvalidArgumentException('El título es obligatorio.');
        }
        if ($autor === null || $autor === '') {
            throw new InvalidArgumentException('El autor es obligatorio.');
        }
        if ($isbn === null || $isbn === '') {
            throw new InvalidArgumentException('El ISBN es obligatorio.');
        }
        if ($anio === null || $anio < 0) {
            throw new InvalidArgumentException('El año debe ser un número positivo.');
        }
        if ($cantidad === null || $cantidad < 0) {
            throw new InvalidArgumentException('La cantidad debe ser un número mayor o igual a cero.');
        }
    }

    if ($titulo !== null && $titulo !== '' && mb_strlen($titulo) > 255) {
        throw new InvalidArgumentException('El título no puede exceder los 255 caracteres.');
    }

    if ($autor !== null && $autor !== '' && mb_strlen($autor) > 255) {
        throw new InvalidArgumentException('El autor no puede exceder los 255 caracteres.');
    }

    if ($isbn !== null && $isbn !== '' && mb_strlen($isbn) > 45) {
        throw new InvalidArgumentException('El ISBN no puede exceder los 45 caracteres.');
    }

    if ($anio !== null && ($anio < 0 || $anio > (int) date('Y') + 1)) {
        throw new InvalidArgumentException('El año ingresado no es válido.');
    }

    if ($cantidad !== null && $cantidad < 0) {
        throw new InvalidArgumentException('La cantidad debe ser un número mayor o igual a cero.');
    }

    return [
        'titulo' => $titulo,
        'autor' => $autor,
        'isbn' => $isbn,
        'anio' => $anio,
        'cantidad' => $cantidad,
    ];
}

function addBook(array $data): int
{
    $clean = validateBookData($data);

    $connection = getMysqliConnection();
    $stmt = $connection->prepare(
        'INSERT INTO libros (titulo, autor, isbn, anio_publicacion, cantidad_disponible) VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'sssii',
        $clean['titulo'],
        $clean['autor'],
        $clean['isbn'],
        $clean['anio'],
        $clean['cantidad']
    );

    $stmt->execute();

    return (int) $connection->insert_id;
}

function updateBook(int $id, array $data): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de libro inválido.');
    }

    $clean = validateBookData($data, true);

    $fields = [];
    $params = [];
    $types = '';

    $columnMap = [
        'titulo' => 'titulo',
        'autor' => 'autor',
        'isbn' => 'isbn',
        'anio' => 'anio_publicacion',
        'cantidad' => 'cantidad_disponible',
    ];

    foreach ($clean as $column => $value) {
        if ($value === null) {
            continue;
        }

        $dbColumn = $columnMap[$column] ?? null;
        if ($dbColumn === null) {
            continue;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                continue;
            }
            $types .= 's';
        } else {
            $types .= 'i';
        }

        $params[] = $value;
        $fields[] = sprintf('%s = ?', $dbColumn);
    }

    if (! $fields) {
        throw new InvalidArgumentException('Debe proporcionar al menos un campo para actualizar.');
    }

    $params[] = $id;
    $types .= 'i';

    $sql = 'UPDATE libros SET ' . implode(', ', $fields) . ' WHERE id = ?';

    $connection = getMysqliConnection();
    $stmt = $connection->prepare($sql);
    $stmt->bind_param($types, ...$params);

    $stmt->execute();

    return $stmt->affected_rows > 0;
}

function deleteBook(int $id): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de libro inválido.');
    }

    $connection = getMysqliConnection();
    $stmt = $connection->prepare('DELETE FROM libros WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

/**
 * @return array{data:list<array<string,mixed>>,total:int,page:int,per_page:int}
 */
function listBooks(int $page, int $perPage, ?string $search = null): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $connection = getMysqliConnection();

    $conditions = [];
    $types = '';
    $params = [];

    if ($search !== null && $search !== '') {
        $searchTerm = '%' . trim($search) . '%';
        $conditions[] = '(titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?)';
        $types .= 'sss';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

    $countSql = 'SELECT COUNT(*) FROM libros' . $where;
    $countStmt = $connection->prepare($countSql);
    if ($types !== '') {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = (int) ($countResult->fetch_row()[0] ?? 0);

    $sql = 'SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros'
        . $where . ' ORDER BY titulo ASC LIMIT ? OFFSET ?';

    $listStmt = $connection->prepare($sql);

    $typesWithLimit = $types . 'ii';
    $paramsWithLimit = $params;
    $paramsWithLimit[] = $perPage;
    $paramsWithLimit[] = $offset;

    $listStmt->bind_param($typesWithLimit, ...$paramsWithLimit);
    $listStmt->execute();
    $result = $listStmt->get_result();

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

function getBookById(int $id): ?array
{
    $connection = getMysqliConnection();
    $stmt = $connection->prepare('SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc() ?: null;
}