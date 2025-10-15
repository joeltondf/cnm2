<?php

namespace App\Security;

class CsrfTokenManager
{
    private const SESSION_KEY = '_csrf_tokens';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    public function generateToken(string $formName): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY][$formName] = $token;

        return $token;
    }

    public function isTokenValid(string $formName, ?string $token): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY][$formName])) {
            return false;
        }

        $isValid = hash_equals($_SESSION[self::SESSION_KEY][$formName], (string) $token);
        if ($isValid) {
            unset($_SESSION[self::SESSION_KEY][$formName]);
        }

        return $isValid;
    }
}
