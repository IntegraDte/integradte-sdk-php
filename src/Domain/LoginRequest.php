<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Credenciales para POST /api/v1/auth/login (ruta publica, sin x-api-key).
 * `data.xUserKey` de la respuesta es el header `x-user-key` para createFirstBusiness.
 */
final class LoginRequest
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
