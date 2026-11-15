<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Cohorte.
 */
class CohortePolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'cohorte.ver');
    }

    public function view(?User $user, $cohorte = null): bool
    {
        return $this->verificarPermiso($user, 'cohorte.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'cohorte.crear');
    }

    public function update(?User $user, $cohorte = null): bool
    {
        return $this->verificarPermiso($user, 'cohorte.editar');
    }

    public function delete(?User $user, $cohorte = null): bool
    {
        return $this->verificarPermiso($user, 'cohorte.eliminar');
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

