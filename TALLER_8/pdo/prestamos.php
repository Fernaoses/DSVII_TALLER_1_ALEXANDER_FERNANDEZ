<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

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

    $pdo = getPdoConnection();
    $pdo->beginTransaction();

    try {
        $stockStmt = $pdo->prepare('SELECT cantidad_disponible FROM libros WHERE id = :id FOR UPDATE');
        $stockStmt->execute(['id' => $libroId]);
        $stock = $stockStmt->fetch();

        if (! $stock) {
            throw new RuntimeException('El libro solicitado no existe.');
        }

        if ((int) $stock['cantidad_disponible'] <= 0) {
            throw new RuntimeException('No hay copias disponibles para el libro seleccionado.');
        }

        $loanStmt = $pdo->prepare(
            'INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_limite, devuelto) VALUES (:libro_id, :usuario_id, NOW(), :fecha_limite, 0)'
        );
        $loanStmt->execute([
            'libro_id' => $libroId,
            'usuario_id' => $usuarioId,
            'fecha_limite' => $fechaLimiteFormatted,
        ]);
        $prestamoId = (int) $pdo->lastInsertId();

        $updateStmt = $pdo->prepare('UPDATE libros SET cantidad_disponible = cantidad_disponible - 1 WHERE id = :id');
        $updateStmt->execute(['id' => $libroId]);

        $pdo->commit();

        return $prestamoId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function registerReturn(int $prestamoId): bool
{
    if ($prestamoId <= 0) {
        throw new InvalidArgumentException('Identificador de préstamo inválido.');
    }

    $pdo = getPdoConnection();
    $pdo->beginTransaction();

    try {
        $loanStmt = $pdo->prepare('SELECT libro_id, devuelto FROM prestamos WHERE id = :id FOR UPDATE');
        $loanStmt->execute(['id' => $prestamoId]);
        $loan = $loanStmt->fetch();

        if (! $loan) {
            throw new RuntimeException('El préstamo solicitado no existe.');
        }

        if ((int) $loan['devuelto'] === 1) {
            $pdo->rollBack();
            return false;
        }

        $updateLoan = $pdo->prepare('UPDATE prestamos SET devuelto = 1, fecha_devolucion = NOW() WHERE id = :id');
        $updateLoan->execute(['id' => $prestamoId]);

        $updateBook = $pdo->prepare('UPDATE libros SET cantidad_disponible = cantidad_disponible + 1 WHERE id = :libro_id');
        $updateBook->execute(['libro_id' => $loan['libro_id']]);

        $pdo->commit();

        return true;
    } catch (Throwable $exception) {
        $pdo->rollBack();
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

    $pdo = getPdoConnection();

    $conditions = ['p.devuelto = 0'];
    $params = [];

    if ($search !== null && $search !== '') {
        $term = '%' . trim($search) . '%';
        $conditions[] = '(l.titulo LIKE :search OR l.isbn LIKE :search OR u.nombre LIKE :search OR u.email LIKE :search)';
        $params['search'] = $term;
    }

    $where = 'WHERE ' . implode(' AND ', $conditions);

    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM prestamos p INNER JOIN libros l ON p.libro_id = l.id INNER JOIN usuarios u ON p.usuario_id = u.id '
        . $where
    );
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $listSql = 'SELECT p.id, l.titulo AS libro, l.isbn, u.nombre AS usuario, u.email, p.fecha_prestamo, p.fecha_limite '
        . 'FROM prestamos p '
        . 'INNER JOIN libros l ON p.libro_id = l.id '
        . 'INNER JOIN usuarios u ON p.usuario_id = u.id '
        . $where . ' ORDER BY p.fecha_prestamo DESC LIMIT :limit OFFSET :offset';

    $listStmt = $pdo->prepare($listSql);
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

    $pdo = getPdoConnection();

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM prestamos WHERE usuario_id = :usuario_id');
    $countStmt->execute(['usuario_id' => $usuarioId]);
    $total = (int) $countStmt->fetchColumn();

    $listStmt = $pdo->prepare(
        'SELECT p.id, l.titulo AS libro, p.fecha_prestamo, p.fecha_limite, p.fecha_devolucion, p.devuelto '
        . 'FROM prestamos p INNER JOIN libros l ON p.libro_id = l.id '
        . 'WHERE p.usuario_id = :usuario_id ORDER BY p.fecha_prestamo DESC LIMIT :limit OFFSET :offset'
    );
    $listStmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
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