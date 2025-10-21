<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/libros.php';
require_once __DIR__ . '/usuarios.php';

function registerLoan(int $libroId, int $usuarioId, ?string $fechaLimite = null): int
{
    if ($libroId <= 0 || $usuarioId <= 0) {
        throw new InvalidArgumentException('Los identificadores proporcionados son inválidos.');
    }

    $fechaLimiteFormatted = null;
    if ($fechaLimite !== null && $fechaLimite !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $fechaLimite);
        if ($date === false) {
            throw new InvalidArgumentException('La fecha límite debe tener el formato YYYY-MM-DD.');
        }
        $fechaLimiteFormatted = $date->format('Y-m-d');
    }

    $connection = getMysqliConnection();

    $connection->begin_transaction();

    try {
        $stockStmt = $connection->prepare('SELECT cantidad_disponible FROM libros WHERE id = ? FOR UPDATE');
        $stockStmt->bind_param('i', $libroId);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        $row = $stockResult->fetch_assoc();

        if (! $row) {
            throw new RuntimeException('El libro solicitado no existe.');
        }

        if ((int) $row['cantidad_disponible'] <= 0) {
            throw new RuntimeException('No hay copias disponibles para el libro seleccionado.');
        }

        $loanStmt = $connection->prepare(
            'INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_limite, devuelto) VALUES (?, ?, NOW(), NULLIF(?, \'\'), 0)'
        );
        $fechaLimiteParam = $fechaLimiteFormatted ?? '';
        $loanStmt->bind_param('iis', $libroId, $usuarioId, $fechaLimiteParam);
        $loanStmt->execute();
        $prestamoId = (int) $connection->insert_id;

        $updateStmt = $connection->prepare(
            'UPDATE libros SET cantidad_disponible = cantidad_disponible - 1 WHERE id = ?'
        );
        $updateStmt->bind_param('i', $libroId);
        $updateStmt->execute();

        $connection->commit();

        return $prestamoId;
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function registerReturn(int $prestamoId): bool
{
    if ($prestamoId <= 0) {
        throw new InvalidArgumentException('Identificador de préstamo inválido.');
    }

    $connection = getMysqliConnection();
    $connection->begin_transaction();

    try {
        $loanStmt = $connection->prepare(
            'SELECT libro_id, devuelto FROM prestamos WHERE id = ? FOR UPDATE'
        );
        $loanStmt->bind_param('i', $prestamoId);
        $loanStmt->execute();
        $result = $loanStmt->get_result();
        $loan = $result->fetch_assoc();

        if (! $loan) {
            throw new RuntimeException('El préstamo solicitado no existe.');
        }

        if ((int) $loan['devuelto'] === 1) {
            $connection->rollback();
            return false;
        }

        $updateLoanStmt = $connection->prepare(
            'UPDATE prestamos SET devuelto = 1, fecha_devolucion = NOW() WHERE id = ?'
        );
        $updateLoanStmt->bind_param('i', $prestamoId);
        $updateLoanStmt->execute();

        $updateBookStmt = $connection->prepare(
            'UPDATE libros SET cantidad_disponible = cantidad_disponible + 1 WHERE id = ?'
        );
        $updateBookStmt->bind_param('i', $loan['libro_id']);
        $updateBookStmt->execute();

        $connection->commit();

        return true;
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

/**
 * @return array{data:list<array<string,mixed>>,total:int,page:int,per_page:int}
 */
function listActiveLoans(int $page, int $perPage, ?string $search = null): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $connection = getMysqliConnection();

    $conditions = ['p.devuelto = 0'];
    $params = [];
    $types = '';

    if ($search !== null && $search !== '') {
        $term = '%' . trim($search) . '%';
        $conditions[] = '(l.titulo LIKE ? OR l.isbn LIKE ? OR u.nombre LIKE ? OR u.email LIKE ?)';
        $params = [$term, $term, $term, $term];
        $types = 'ssss';
    }

    $where = 'WHERE ' . implode(' AND ', $conditions);

    $countSql = 'SELECT COUNT(*) FROM prestamos p INNER JOIN libros l ON p.libro_id = l.id '
        . 'INNER JOIN usuarios u ON p.usuario_id = u.id ' . $where;
    $countStmt = $connection->prepare($countSql);
    if ($types !== '') {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);

    $sql = 'SELECT p.id, l.titulo AS libro, l.isbn, u.nombre AS usuario, u.email, p.fecha_prestamo, p.fecha_limite '
        . 'FROM prestamos p '
        . 'INNER JOIN libros l ON p.libro_id = l.id '
        . 'INNER JOIN usuarios u ON p.usuario_id = u.id '
        . $where
        . ' ORDER BY p.fecha_prestamo DESC LIMIT ? OFFSET ?';

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

/**
 * @return array{data:list<array<string,mixed>>,total:int,page:int,per_page:int}
 */
function getLoanHistoryByUser(int $usuarioId, int $page, int $perPage): array
{
    if ($usuarioId <= 0) {
        throw new InvalidArgumentException('Identificador de usuario inválido.');
    }

    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $connection = getMysqliConnection();

    $countSql = 'SELECT COUNT(*) FROM prestamos WHERE usuario_id = ?';
    $countStmt = $connection->prepare($countSql);
    $countStmt->bind_param('i', $usuarioId);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);

    $sql = 'SELECT p.id, l.titulo AS libro, p.fecha_prestamo, p.fecha_limite, p.fecha_devolucion, p.devuelto '
        . 'FROM prestamos p INNER JOIN libros l ON p.libro_id = l.id '
        . 'WHERE p.usuario_id = ? ORDER BY p.fecha_prestamo DESC LIMIT ? OFFSET ?';

    $stmt = $connection->prepare($sql);
    $stmt->bind_param('iii', $usuarioId, $perPage, $offset);
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