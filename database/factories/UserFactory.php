<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory para la entidad `users` según `sogac_mio.sql` (`username`, `status_id`, `estatus`).
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 *
 * Historial:
 *  - 2026-11-14: Reemplazo de `name` por `username` e inicialización de `status_id` y `estatus`.
 *  - 2026-11-16: Corrección PHP 8.3 reemplazando lectura directa de constante del trait por `User::STATUS_ACTIVO`.
 */
class UserFactory extends Factory
{
    /**
     * Contraseña compartida por defecto en pruebas.
     */
    protected static ?string $password;

    /**
     * Estado por defecto del modelo `User`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username'                  => fake()->unique()->userName(),
            'email'                     => fake()->unique()->safeEmail(),
            'email_verified_at'         => now(),
            'password'                  => static::$password ??= Hash::make('password'),
            'remember_token'            => Str::random(10),
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
            'estatus'                   => true,
            'status_id'                 => User::STATUS_ACTIVO,
        ];
    }

    /**
     * Marca el correo del usuario como no verificado.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Configura autenticación en dos factores.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret'         => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at'   => now(),
        ]);
    }
}
