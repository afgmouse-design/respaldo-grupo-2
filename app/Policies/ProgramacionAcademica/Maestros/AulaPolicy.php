<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Aula.
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class AulaPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'aula.ver');
    }

    public function view(?User $user, $aula = null): bool
    {
        return $this->permiso($user, 'aula.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'aula.crear');
    }

    public function update(?User $user, $aula = null): bool
    {
        return $this->permiso($user, 'aula.editar');
    }

    public function delete(?User $user, $aula = null): bool
    {
        return $this->permiso($user, 'aula.eliminar');
    }
}
