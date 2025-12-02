<?php
require_once __DIR__ . '/../config/db.php';

class UserRepository
{
    public function findOrCreateByGoogle(string $googleId, string $email, string $name): array
    {
        $db = get_db_connection();

        $stmt = $db->prepare('SELECT id, email, nombre, google_id, fecha_registro FROM usuarios WHERE google_id = ?');
        $stmt->bind_param('s', $googleId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return $row;
        }

        $stmt->close();

        $insert = $db->prepare('INSERT INTO usuarios (email, nombre, google_id, fecha_registro) VALUES (?, ?, ?, NOW())');
        $insert->bind_param('sss', $email, $name, $googleId);
        $insert->execute();

        $userId = $db->insert_id;
        $insert->close();
        $db->close();

        return [
            'id' => $userId,
            'email' => $email,
            'nombre' => $name,
            'google_id' => $googleId,
            'fecha_registro' => date('Y-m-d H:i:s')
        ];
    }
}