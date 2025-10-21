<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * @param array<string, mixed> $data
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
    $pdo = getPdoConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO libros (titulo, autor, isbn, anio_publicacion, cantidad_disponible) VALUES (:titulo, :autor, :isbn, :anio, :cantidad)'
    );
    $stmt->execute([
        'titulo' => $clean['titulo'],
        'autor' => $clean['autor'],
        'isbn' => $clean['isbn'],
        'anio' => $clean['anio'],
        'cantidad' => $clean['cantidad'],
    ]);

    return (int) $pdo->lastInsertId();
}

function updateBook(int $id, array $data): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de libro inválido.');
    }

    $clean = validateBookData($data, true);

    $fields = [];
    $params = ['id' => $id];

    $map = [
        'titulo' => 'titulo',
        'autor' => 'autor',
        'isbn' => 'isbn',
        'anio' => 'anio_publicacion',
        'cantidad' => 'cantidad_disponible',
    ];

    foreach ($clean as $key => $value) {
        if ($value === null) {
            continue;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                continue;
            }
        }

        $dbColumn = $map[$key] ?? null;
        if ($dbColumn === null) {
            continue;
        }

        $fields[] = sprintf('%s = :%s', $dbColumn, $key);
        $params[$key] = $value;
    }

    if (! $fields) {
        throw new InvalidArgumentException('Debe proporcionar al menos un campo para actualizar.');
    }

    $sql = 'UPDATE libros SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $pdo = getPdoConnection();
    $stmt = $pdo->prepare($sql);

    return $stmt->execute($params);
}

function deleteBook(int $id): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Identificador de libro inválido.');
    }

    $pdo = getPdoConnection();
    $stmt = $pdo->prepare('DELETE FROM libros WHERE id = :id');

    return $stmt->execute(['id' => $id]);
}

/**
 * @return array{data:list<array<string,mixed>>,total:int,page:int,per_page:int}
 */
function listBooks(int $page, int $perPage, ?string $search = null): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $pdo = getPdoConnection();

    $conditions = [];
    $params = [];

    if ($search !== null && $search !== '') {
        $searchTerm = '%' . trim($search) . '%';
        $conditions[] = '(titulo LIKE :search OR autor LIKE :search OR isbn LIKE :search)';
        $params['search'] = $searchTerm;
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM libros' . $where);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $listStmt = $pdo->prepare(
        'SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros'
        . $where . ' ORDER BY titulo ASC LIMIT :limit OFFSET :offset'
    );

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

function getBookById(int $id): ?array
{
    $pdo = getPdoConnection();
    $stmt = $pdo->prepare('SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}