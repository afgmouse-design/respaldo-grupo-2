<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Acción Fortify: Registro de nuevo usuario en `users`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` usando `username`, `status_id = 1` y `estatus = true`.
 *  - 2026-11-16: Corrección PHP 8.3 reemplazando lectura directa de constante de trait por `User::STATUS_ACTIVO`.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Valida y registra un usuario nuevo.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'username'  => $input['username'],
            'email'     => $input['email'],
            'password'  => $input['password'],
            'estatus'   => true,
            'status_id' => User::STATUS_ACTIVO,
        ]);
    }
}
