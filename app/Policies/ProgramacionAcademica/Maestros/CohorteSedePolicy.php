<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Cohorte por Sede.
 */
class CohorteSedePolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'cohorte_sede.ver');
    }

    public function view(?User $user, $cohorteSede = null): bool
    {
        return $this->verificarPermiso($user, 'cohorte_sede.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'cohorte_sede.crear');
    }

    public function update(?User $user, $cohorteSede = null): bool
    {
        return $this->verificarPermiso($user, 'cohorte_sede.editar');
    }

    public function delete(?User $user, $cohorteSede = null): bool
    {
        return $this->verificarPermiso($user, 'cohorte_sede.eliminar');
    }

    protected function verificarPermiso(?User $user, string ...$permisos): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasPermissionTo')) {
            foreach ($permisos as $permiso) {
                if ($user->hasPermissionTo($permiso)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }
}

