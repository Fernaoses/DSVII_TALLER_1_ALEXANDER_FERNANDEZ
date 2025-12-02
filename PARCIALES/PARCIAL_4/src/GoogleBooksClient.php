<?php
require_once __DIR__ . '/../config/google.php';

class GoogleBooksClient
{
    private const BASE_URL = 'https://www.googleapis.com/books/v1/volumes';

    public function search(string $query): array
    {
        $encoded = urlencode($query);
        $url = self::BASE_URL . "?q={$encoded}&maxResults=10&key=" . GOOGLE_BOOKS_API_KEY;

        $response = file_get_contents($url);
        if ($response === false) {
            throw new RuntimeException('No se pudo contactar la API de Google Books');
        }

        $data = json_decode($response, true);
        $books = [];

        foreach ($data['items'] ?? [] as $item) {
            $info = $item['volumeInfo'];
            $books[] = [
                'id' => $item['id'],
                'title' => $info['title'] ?? 'Título desconocido',
                'author' => $info['authors'][0] ?? 'Autor no disponible',
                'thumbnail' => $info['imageLinks']['thumbnail'] ?? '',
                'description' => $info['description'] ?? 'Sin descripción'
            ];
        }

        return $books;
    }
}