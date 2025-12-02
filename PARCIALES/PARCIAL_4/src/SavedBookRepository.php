<?php
require_once __DIR__ . '/../config/db.php';

class SavedBookRepository
{
    public function saveBook(array $book, int $userId): void
    {
        $db = get_db_connection();

        $stmt = $db->prepare(
            'INSERT INTO libros_guardados (user_id, google_books_id, titulo, autor, imagen_portada, reseña_personal, fecha_guardado)
            VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        $stmt->bind_param(
            'isssss',
            $userId,
            $book['id'],
            $book['title'],
            $book['author'],
            $book['thumbnail'],
            $book['review']
        );

        $stmt->execute();
        $stmt->close();
        $db->close();
    }

    public function listByUser(int $userId): array
    {
        $db = get_db_connection();
        $stmt = $db->prepare(
            'SELECT id, google_books_id, titulo, autor, imagen_portada, reseña_personal, fecha_guardado
            FROM libros_guardados WHERE user_id = ? ORDER BY fecha_guardado DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $db->close();
        return $books;
    }

    public function deleteBook(int $bookId, int $userId): void
    {
        $db = get_db_connection();
        $stmt = $db->prepare('DELETE FROM libros_guardados WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $bookId, $userId);
        $stmt->execute();
        $stmt->close();
        $db->close();
    }
}