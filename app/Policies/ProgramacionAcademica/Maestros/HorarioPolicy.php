<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Horario.
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso` y eliminar bypass sin control.
 */
class HorarioPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'horario.ver');
    }

    public function view(?User $user, $horario = null): bool
    {
        return $this->permiso($user, 'horario.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'horario.crear');
    }

    public function update(?User $user, $horario = null): bool
    {
        return $this->permiso($user, 'horario.editar');
    }

    public function delete(?User $user, $horario = null): bool
    {
        return $this->permiso($user, 'horario.eliminar');
    }
}
