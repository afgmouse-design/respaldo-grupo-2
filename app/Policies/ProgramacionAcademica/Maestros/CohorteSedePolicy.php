<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Cohorte por Sede.
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class CohorteSedePolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'cohorte_sede.ver');
    }

    public function view(?User $user, $cohorteSede = null): bool
    {
        return $this->permiso($user, 'cohorte_sede.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'cohorte_sede.crear');
    }

    public function update(?User $user, $cohorteSede = null): bool
    {
        return $this->permiso($user, 'cohorte_sede.editar');
    }

    public function delete(?User $user, $cohorteSede = null): bool
    {
        return $this->permiso($user, 'cohorte_sede.eliminar');
    }
}
