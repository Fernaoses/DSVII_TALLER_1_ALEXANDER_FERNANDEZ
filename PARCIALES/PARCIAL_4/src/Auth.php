<?php
require_once __DIR__ . '/../config/google.php';
require_once __DIR__ . '/UserRepository.php';

class Auth
{
    private UserRepository $users;

    public function __construct()
    {
        session_start();
        $this->users = new UserRepository();
    }

    public function getLoginUrl(): string
    {
        $params = http_build_query([
            'client_id' => GOOGLE_CLIENT_ID,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'prompt' => 'select_account'
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    public function handleGoogleCallback(): void
    {
        if (!isset($_GET['code'])) {
            throw new InvalidArgumentException('Código de autorización no presente.');
        }

        // Flujo simplificado: en una app real intercambiarías el código por un token.
        // Aquí simulamos la respuesta de Google para mantener el ejemplo autocontenido.
        $fakePayload = $this->simulateTokenPayload($_GET['code']);

        $user = $this->users->findOrCreateByGoogle(
            $fakePayload['sub'],
            $fakePayload['email'],
            $fakePayload['name']
        );

        $_SESSION['user'] = $user;
    }

    public function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: index.php');
            exit;
        }
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user']);
    }

    public function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function logout(): void
    {
        session_destroy();
    }

    private function simulateTokenPayload(string $code): array
    {
        // Esta función solo existe para que el flujo de la práctica funcione sin credenciales reales.
        // Reemplázala por el intercambio de tokens en producción.
        return [
            'sub' => hash('sha256', $code),
            'email' => 'demo+' . substr($code, 0, 6) . '@example.com',
            'name' => 'Usuario Google'
        ];
    }
}