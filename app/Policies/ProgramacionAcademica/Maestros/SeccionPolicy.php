<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Sección (Programación Académica).
 * Centraliza permisos seccion.ver / crear / editar / eliminar.
 */
class SeccionPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'seccion.ver');
    }

    public function view(?User $user, $seccion = null): bool
    {
        return $this->verificarPermiso($user, 'seccion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'seccion.crear');
    }

    public function update(?User $user, $seccion = null): bool
    {
        return $this->verificarPermiso($user, 'seccion.editar');
    }

    public function delete(?User $user, $seccion = null): bool
    {
        return $this->verificarPermiso($user, 'seccion.eliminar');
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

