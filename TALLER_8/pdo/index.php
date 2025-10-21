<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/libros.php';
require_once __DIR__ . '/usuarios.php';
require_once __DIR__ . '/prestamos.php';

const ITEMS_PER_PAGE = 5;

function renderPagination(string $module, string $pageParam, int $total, int $perPage, int $currentPage, array $extraParams = []): string
{
    $totalPages = (int) ceil($total / $perPage);

    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav class="pagination">';

    for ($page = 1; $page <= $totalPages; $page++) {
        $params = array_merge($extraParams, ['module' => $module, $pageParam => $page]);
        $url = '?' . http_build_query($params);
        if ($page === $currentPage) {
            $html .= '<span class="current">' . e((string) $page) . '</span>';
        } else {
            $html .= '<a href="' . e($url) . '">' . e((string) $page) . '</a>';
        }
    }

    $html .= '</nav>';

    return $html;
}

$module = filter_input(INPUT_GET, 'module', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'libros';
if (! in_array($module, ['libros', 'usuarios', 'prestamos'], true)) {
    $module = 'libros';
}

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'form_action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';

    try {
        switch ($module) {
            case 'libros':
                switch ($action) {
                    case 'create_book':
                        $bookId = addBook([
                            'titulo' => filter_input(INPUT_POST, 'titulo', FILTER_UNSAFE_RAW),
                            'autor' => filter_input(INPUT_POST, 'autor', FILTER_UNSAFE_RAW),
                            'isbn' => filter_input(INPUT_POST, 'isbn', FILTER_UNSAFE_RAW),
                            'anio' => filter_input(INPUT_POST, 'anio', FILTER_UNSAFE_RAW),
                            'cantidad' => filter_input(INPUT_POST, 'cantidad', FILTER_UNSAFE_RAW),
                        ]);
                        $messages[] = 'Libro registrado con éxito. ID: ' . $bookId;
                        break;
                    case 'update_book':
                        $bookId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                        if ($bookId === false || $bookId === null) {
                            throw new InvalidArgumentException('Debe proporcionar un ID válido para actualizar.');
                        }
                        $updated = updateBook($bookId, [
                            'titulo' => filter_input(INPUT_POST, 'titulo', FILTER_UNSAFE_RAW),
                            'autor' => filter_input(INPUT_POST, 'autor', FILTER_UNSAFE_RAW),
                            'isbn' => filter_input(INPUT_POST, 'isbn', FILTER_UNSAFE_RAW),
                            'anio' => filter_input(INPUT_POST, 'anio', FILTER_UNSAFE_RAW),
                            'cantidad' => filter_input(INPUT_POST, 'cantidad', FILTER_UNSAFE_RAW),
                        ]);
                        $messages[] = $updated ? 'Libro actualizado correctamente.' : 'No se realizaron cambios.';
                        break;
                    case 'delete_book':
                        $bookId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                        if ($bookId === false || $bookId === null) {
                            throw new InvalidArgumentException('Debe proporcionar un ID válido para eliminar.');
                        }
                        $deleted = deleteBook($bookId);
                        $messages[] = $deleted ? 'Libro eliminado correctamente.' : 'No se encontró el libro solicitado.';
                        break;
                }
                break;
            case 'usuarios':
                switch ($action) {
                    case 'create_user':
                        $userId = createUser([
                            'nombre' => filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW),
                            'email' => filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW),
                            'password' => filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW),
                        ]);
                        $messages[] = 'Usuario registrado con éxito. ID: ' . $userId;
                        break;
                    case 'update_user':
                        $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                        if ($userId === false || $userId === null) {
                            throw new InvalidArgumentException('Debe proporcionar un ID válido para actualizar.');
                        }
                        $updated = updateUser($userId, [
                            'nombre' => filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW),
                            'email' => filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW),
                            'password' => filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW),
                        ]);
                        $messages[] = $updated ? 'Usuario actualizado correctamente.' : 'No se realizaron cambios.';
                        break;
                    case 'delete_user':
                        $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                        if ($userId === false || $userId === null) {
                            throw new InvalidArgumentException('Debe proporcionar un ID válido para eliminar.');
                        }
                        $deleted = deleteUser($userId);
                        $messages[] = $deleted ? 'Usuario eliminado correctamente.' : 'No se encontró el usuario solicitado.';
                        break;
                }
                break;
            case 'prestamos':
                switch ($action) {
                    case 'create_loan':
                        $loanId = registerLoan(
                            (int) filter_input(INPUT_POST, 'libro_id', FILTER_VALIDATE_INT),
                            (int) filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT),
                            filter_input(INPUT_POST, 'fecha_limite', FILTER_UNSAFE_RAW)
                        );
                        $messages[] = 'Préstamo registrado con éxito. ID: ' . $loanId;
                        break;
                    case 'return_loan':
                        $prestamoId = filter_input(INPUT_POST, 'prestamo_id', FILTER_VALIDATE_INT);
                        if ($prestamoId === false || $prestamoId === null) {
                            throw new InvalidArgumentException('Debe proporcionar un ID de préstamo válido.');
                        }
                        $returned = registerReturn($prestamoId);
                        $messages[] = $returned ? 'Devolución registrada correctamente.' : 'El préstamo ya estaba devuelto.';
                        break;
                }
                break;
        }
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

$bookPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$bookSearch = filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW) ?: null;
$books = listBooks($bookPage, ITEMS_PER_PAGE, $bookSearch);

$userPage = filter_input(INPUT_GET, 'user_page', FILTER_VALIDATE_INT) ?: 1;
$userSearch = filter_input(INPUT_GET, 'user_q', FILTER_UNSAFE_RAW) ?: null;
$users = listUsers($userPage, ITEMS_PER_PAGE, $userSearch);

$loanPage = filter_input(INPUT_GET, 'loan_page', FILTER_VALIDATE_INT) ?: 1;
$loanSearch = filter_input(INPUT_GET, 'loan_q', FILTER_UNSAFE_RAW) ?: null;
$activeLoans = listActiveLoans($loanPage, ITEMS_PER_PAGE, $loanSearch);

$historyUserId = filter_input(INPUT_GET, 'history_user_id', FILTER_VALIDATE_INT) ?: null;
$historyPage = filter_input(INPUT_GET, 'history_page', FILTER_VALIDATE_INT) ?: 1;
$loanHistory = null;
if ($historyUserId) {
    try {
        $loanHistory = getLoanHistoryByUser($historyUserId, $historyPage, ITEMS_PER_PAGE);
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Biblioteca (PDO)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        header { background: #00695c; color: #fff; padding: 1rem; }
        header h1 { margin: 0; }
        nav a { color: #fff; margin-right: 1rem; text-decoration: none; font-weight: bold; }
        nav a.active { text-decoration: underline; }
        main { padding: 1rem; }
        section { background: #fff; padding: 1rem; margin-bottom: 1.5rem; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; }
        form { margin-bottom: 1rem; display: grid; gap: 0.5rem; }
        form label { font-weight: bold; }
        input[type="text"], input[type="number"], input[type="email"], input[type="password"], input[type="date"] { padding: 0.4rem; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #00695c; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
        button:hover { background: #00564c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
        th { background: #f0f0f0; }
        .messages, .errors { margin: 1rem; padding: 0.5rem 1rem; border-radius: 4px; }
        .messages { background: #e0f2f1; color: #004d40; }
        .errors { background: #fbeaea; color: #b71c1c; }
        .pagination { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
        .pagination a { padding: 0.3rem 0.6rem; border-radius: 3px; background: #ececec; text-decoration: none; color: #333; }
        .pagination .current { padding: 0.3rem 0.6rem; border-radius: 3px; background: #00695c; color: #fff; }
    </style>
</head>
<body>
<header>
    <h1>Sistema de Gestión de Biblioteca (PDO)</h1>
    <nav>
        <a href="?module=libros" class="<?= $module === 'libros' ? 'active' : '' ?>">Libros</a>
        <a href="?module=usuarios" class="<?= $module === 'usuarios' ? 'active' : '' ?>">Usuarios</a>
        <a href="?module=prestamos" class="<?= $module === 'prestamos' ? 'active' : '' ?>">Préstamos</a>
    </nav>
</header>
<main>
    <?php if ($messages): ?>
        <div class="messages">
            <ul>
                <?php foreach ($messages as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($module === 'libros'): ?>
        <section>
            <h2>Registrar Libro</h2>
            <form method="post" action="?module=libros">
                <input type="hidden" name="form_action" value="create_book">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" required>
                <label for="autor">Autor</label>
                <input type="text" name="autor" id="autor" required>
                <label for="isbn">ISBN</label>
                <input type="text" name="isbn" id="isbn" required>
                <label for="anio">Año de publicación</label>
                <input type="number" name="anio" id="anio" required>
                <label for="cantidad">Cantidad disponible</label>
                <input type="number" name="cantidad" id="cantidad" min="0" required>
                <button type="submit">Guardar</button>
            </form>
        </section>

        <section>
            <h2>Actualizar Libro</h2>
            <form method="post" action="?module=libros">
                <input type="hidden" name="form_action" value="update_book">
                <label for="update_id">ID del libro</label>
                <input type="number" name="id" id="update_id" min="1" required>
                <label for="update_titulo">Título</label>
                <input type="text" name="titulo" id="update_titulo">
                <label for="update_autor">Autor</label>
                <input type="text" name="autor" id="update_autor">
                <label for="update_isbn">ISBN</label>
                <input type="text" name="isbn" id="update_isbn">
                <label for="update_anio">Año de publicación</label>
                <input type="number" name="anio" id="update_anio">
                <label for="update_cantidad">Cantidad disponible</label>
                <input type="number" name="cantidad" id="update_cantidad" min="0">
                <button type="submit">Actualizar</button>
            </form>
        </section>

        <section>
            <h2>Eliminar Libro</h2>
            <form method="post" action="?module=libros">
                <input type="hidden" name="form_action" value="delete_book">
                <label for="delete_id">ID del libro</label>
                <input type="number" name="id" id="delete_id" min="1" required>
                <button type="submit">Eliminar</button>
            </form>
        </section>

        <section>
            <h2>Listado de Libros</h2>
            <form method="get" action="">
                <input type="hidden" name="module" value="libros">
                <label for="buscar_libros">Buscar (título, autor o ISBN)</label>
                <input type="text" name="q" id="buscar_libros" value="<?= e((string) ($bookSearch ?? '')) ?>">
                <button type="submit">Buscar</button>
            </form>
            <p>Total de libros: <?= e((string) $books['total']) ?></p>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>ISBN</th>
                        <th>Año</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books['data'] as $book): ?>
                        <tr>
                            <td><?= e((string) $book['id']) ?></td>
                            <td><?= e((string) $book['titulo']) ?></td>
                            <td><?= e((string) $book['autor']) ?></td>
                            <td><?= e((string) $book['isbn']) ?></td>
                            <td><?= e((string) $book['anio_publicacion']) ?></td>
                            <td><?= e((string) $book['cantidad_disponible']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (! $books['data']): ?>
                        <tr><td colspan="6">No hay registros para mostrar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?= renderPagination('libros', 'page', $books['total'], $books['per_page'], $books['page'], ['q' => $bookSearch]) ?>
        </section>
    <?php elseif ($module === 'usuarios'): ?>
        <section>
            <h2>Registrar Usuario</h2>
            <form method="post" action="?module=usuarios">
                <input type="hidden" name="form_action" value="create_user">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" required>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" minlength="6" required>
                <button type="submit">Registrar</button>
            </form>
        </section>

        <section>
            <h2>Actualizar Usuario</h2>
            <form method="post" action="?module=usuarios">
                <input type="hidden" name="form_action" value="update_user">
                <label for="user_id">ID del usuario</label>
                <input type="number" name="id" id="user_id" min="1" required>
                <label for="user_nombre">Nombre</label>
                <input type="text" name="nombre" id="user_nombre">
                <label for="user_email">Email</label>
                <input type="email" name="email" id="user_email">
                <label for="user_password">Contraseña (opcional)</label>
                <input type="password" name="password" id="user_password" minlength="6">
                <button type="submit">Actualizar</button>
            </form>
        </section>

        <section>
            <h2>Eliminar Usuario</h2>
            <form method="post" action="?module=usuarios">
                <input type="hidden" name="form_action" value="delete_user">
                <label for="delete_user_id">ID del usuario</label>
                <input type="number" name="id" id="delete_user_id" min="1" required>
                <button type="submit">Eliminar</button>
            </form>
        </section>

        <section>
            <h2>Listado de Usuarios</h2>
            <form method="get" action="">
                <input type="hidden" name="module" value="usuarios">
                <label for="buscar_usuarios">Buscar (nombre o email)</label>
                <input type="text" name="user_q" id="buscar_usuarios" value="<?= e((string) ($userSearch ?? '')) ?>">
                <button type="submit">Buscar</button>
            </form>
            <p>Total de usuarios: <?= e((string) $users['total']) ?></p>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Registrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users['data'] as $user): ?>
                        <tr>
                            <td><?= e((string) $user['id']) ?></td>
                            <td><?= e((string) $user['nombre']) ?></td>
                            <td><?= e((string) $user['email']) ?></td>
                            <td><?= e((string) $user['creado_en']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (! $users['data']): ?>
                        <tr><td colspan="4">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?= renderPagination('usuarios', 'user_page', $users['total'], $users['per_page'], $users['page'], ['user_q' => $userSearch]) ?>
        </section>
    <?php else: ?>
        <section>
            <h2>Registrar Préstamo</h2>
            <form method="post" action="?module=prestamos">
                <input type="hidden" name="form_action" value="create_loan">
                <label for="prestamo_libro">ID del libro</label>
                <input type="number" name="libro_id" id="prestamo_libro" min="1" required>
                <label for="prestamo_usuario">ID del usuario</label>
                <input type="number" name="usuario_id" id="prestamo_usuario" min="1" required>
                <label for="prestamo_limite">Fecha límite (opcional)</label>
                <input type="date" name="fecha_limite" id="prestamo_limite">
                <button type="submit">Registrar préstamo</button>
            </form>
        </section>

        <section>
            <h2>Registrar Devolución</h2>
            <form method="post" action="?module=prestamos">
                <input type="hidden" name="form_action" value="return_loan">
                <label for="prestamo_id">ID del préstamo</label>
                <input type="number" name="prestamo_id" id="prestamo_id" min="1" required>
                <button type="submit">Registrar devolución</button>
            </form>
        </section>

        <section>
            <h2>Préstamos Activos</h2>
            <form method="get" action="">
                <input type="hidden" name="module" value="prestamos">
                <label for="buscar_prestamos">Buscar por libro o usuario</label>
                <input type="text" name="loan_q" id="buscar_prestamos" value="<?= e((string) ($loanSearch ?? '')) ?>">
                <button type="submit">Buscar</button>
            </form>
            <p>Total de préstamos activos: <?= e((string) $activeLoans['total']) ?></p>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Libro</th>
                        <th>ISBN</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Fecha préstamo</th>
                        <th>Fecha límite</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeLoans['data'] as $loan): ?>
                        <tr>
                            <td><?= e((string) $loan['id']) ?></td>
                            <td><?= e((string) $loan['libro']) ?></td>
                            <td><?= e((string) $loan['isbn']) ?></td>
                            <td><?= e((string) $loan['usuario']) ?></td>
                            <td><?= e((string) $loan['email']) ?></td>
                            <td><?= e((string) $loan['fecha_prestamo']) ?></td>
                            <td><?= e((string) ($loan['fecha_limite'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (! $activeLoans['data']): ?>
                        <tr><td colspan="7">No hay préstamos activos.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?= renderPagination('prestamos', 'loan_page', $activeLoans['total'], $activeLoans['per_page'], $activeLoans['page'], ['loan_q' => $loanSearch]) ?>
        </section>

        <section>
            <h2>Historial por Usuario</h2>
            <form method="get" action="">
                <input type="hidden" name="module" value="prestamos">
                <label for="historial_usuario">ID del usuario</label>
                <input type="number" name="history_user_id" id="historial_usuario" min="1" value="<?= e((string) ($historyUserId ?? '')) ?>">
                <button type="submit">Ver historial</button>
            </form>
            <?php if ($loanHistory): ?>
                <p>Total de préstamos del usuario: <?= e((string) $loanHistory['total']) ?></p>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Libro</th>
                            <th>Fecha préstamo</th>
                            <th>Fecha límite</th>
                            <th>Fecha devolución</th>
                            <th>Devuelto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loanHistory['data'] as $history): ?>
                            <tr>
                                <td><?= e((string) $history['id']) ?></td>
                                <td><?= e((string) $history['libro']) ?></td>
                                <td><?= e((string) $history['fecha_prestamo']) ?></td>
                                <td><?= e((string) ($history['fecha_limite'] ?? '')) ?></td>
                                <td><?= e((string) ($history['fecha_devolucion'] ?? '')) ?></td>
                                <td><?= $history['devuelto'] ? 'Sí' : 'No' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (! $loanHistory['data']): ?>
                            <tr><td colspan="6">No hay registros para este usuario.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?= renderPagination('prestamos', 'history_page', $loanHistory['total'], $loanHistory['per_page'], $loanHistory['page'], [
                    'loan_q' => $loanSearch,
                    'history_user_id' => $historyUserId,
                ]) ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>