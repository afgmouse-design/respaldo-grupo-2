<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Trait de validación reutilizable para el perfil de usuario.
 *
 * Propósito: Validar `username` y `email` de acuerdo con `users` en `sogac_mio.sql`
 * (sin utilizar la columna inexistente `name`).
 *
 * Historial:
 *  - 2026-11-14: Reemplazado campo `name` por `username` único en `users`.
 */
trait ProfileValidationRules
{
    /**
     * Obtiene las reglas de validación del perfil de usuario.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'username' => $this->usernameRules($userId),
            'email'    => $this->emailRules($userId),
        ];
    }

    /**
     * Reglas de validación para `users.username`.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function usernameRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'max:255',
            $userId === null
                ? Rule::unique(User::class, 'username')
                : Rule::unique(User::class, 'username')->ignore($userId),
        ];
    }

    /**
     * Reglas de validación para `users.email`.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class, 'email')
                : Rule::unique(User::class, 'email')->ignore($userId),
        ];
    }
}
