<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/GoogleBooksClient.php';
require_once __DIR__ . '/../src/SavedBookRepository.php';

auth_session_guard();
$auth = new Auth();
$booksClient = new GoogleBooksClient();
$savedBooks = [];
$searchResults = [];

if ($auth->isAuthenticated()) {
    $savedBooks = (new SavedBookRepository())->listByUser($auth->currentUser()['id']);
}

$query = $_GET['q'] ?? '';
if ($query && $auth->isAuthenticated()) {
    $searchResults = $booksClient->search($query);
}

function auth_session_guard(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mini Biblioteca Personal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 0; }
        header { background: #2b6777; color: #fff; padding: 1rem; }
        main { padding: 1rem 2rem; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); padding: 1rem; margin-bottom: 1rem; }
        .flex { display: flex; gap: 1rem; align-items: center; }
        img { width: 80px; height: auto; }
        form { margin: 0; }
        button { background: #52ab98; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
        button.danger { background: #c0392b; }
        input[type="text"], textarea { width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body>
<header>
    <h1>Mini Sistema de Biblioteca Personal</h1>
</header>
<main>
    <?php if (!$auth->isAuthenticated()): ?>
        <div class="card">
            <p>Para comenzar inicia sesión con tu cuenta de Google.</p>
            <a href="<?= htmlspecialchars($auth->getLoginUrl()); ?>">
                <button>Ingresar con Google</button>
            </a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="flex">
                <div>
                    <strong>Hola, <?= htmlspecialchars($auth->currentUser()['nombre']); ?></strong><br>
                    <small><?= htmlspecialchars($auth->currentUser()['email']); ?></small>
                </div>
                <form action="logout.php" method="post">
                    <button type="submit" class="danger">Cerrar sesión</button>
                </form>
            </div>
        </div>

        <div class="card">
            <form method="get">
                <label for="q">Buscar en Google Books</label>
                <input type="text" id="q" name="q" placeholder="Ej: Clean Code" value="<?= htmlspecialchars($query); ?>" required>
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if ($query): ?>
            <div class="card">
                <h2>Resultados</h2>
                <?php if (empty($searchResults)): ?>
                    <p>No se encontraron resultados.</p>
                <?php endif; ?>
                <?php foreach ($searchResults as $book): ?>
                    <div class="flex" style="margin-bottom: 1rem;">
                        <?php if ($book['thumbnail']): ?>
                            <img src="<?= htmlspecialchars($book['thumbnail']); ?>" alt="Portada">
                        <?php endif; ?>
                        <div style="flex:1;">
                            <strong><?= htmlspecialchars($book['title']); ?></strong><br>
                            <small><?= htmlspecialchars($book['author']); ?></small>
                            <p><?= htmlspecialchars($book['description']); ?></p>
                            <form action="save_book.php" method="post">
                                <input type="hidden" name="book_id" value="<?= htmlspecialchars($book['id']); ?>">
                                <input type="hidden" name="title" value="<?= htmlspecialchars($book['title']); ?>">
                                <input type="hidden" name="author" value="<?= htmlspecialchars($book['author']); ?>">
                                <input type="hidden" name="thumbnail" value="<?= htmlspecialchars($book['thumbnail']); ?>">
                                <label>Tu reseña</label>
                                <textarea name="review" rows="2" placeholder="¿Qué te pareció?"></textarea>
                                <button type="submit">Guardar en favoritos</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Mis libros guardados</h2>
            <?php if (empty($savedBooks)): ?>
                <p>Aún no has guardado libros.</p>
            <?php endif; ?>
            <?php foreach ($savedBooks as $book): ?>
                <div class="flex" style="margin-bottom: 1rem;">
                    <?php if ($book['imagen_portada']): ?>
                        <img src="<?= htmlspecialchars($book['imagen_portada']); ?>" alt="Portada">
                    <?php endif; ?>
                    <div style="flex:1;">
                        <strong><?= htmlspecialchars($book['titulo']); ?></strong><br>
                        <small><?= htmlspecialchars($book['autor']); ?></small>
                        <p><?= nl2br(htmlspecialchars($book['reseña_personal'])); ?></p>
                        <form action="delete_book.php" method="post" onsubmit="return confirm('¿Eliminar este libro?');">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']); ?>">
                            <button type="submit" class="danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>