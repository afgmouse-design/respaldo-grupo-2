<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Cohorte.
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class CohortePolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'cohorte.ver');
    }

    public function view(?User $user, $cohorte = null): bool
    {
        return $this->permiso($user, 'cohorte.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'cohorte.crear');
    }

    public function update(?User $user, $cohorte = null): bool
    {
        return $this->permiso($user, 'cohorte.editar');
    }

    public function delete(?User $user, $cohorte = null): bool
    {
        return $this->permiso($user, 'cohorte.eliminar');
    }
}
