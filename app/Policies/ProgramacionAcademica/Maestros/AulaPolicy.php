<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Aula.
 * Centraliza la verificación de los permisos aula.* creados por AulaPermisosSeeder (patrón de SedePolicy).
 */
class AulaPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'aula.ver');
    }

    public function view(?User $user, $aula = null): bool
    {
        return $this->verificarPermiso($user, 'aula.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'aula.crear');
    }

    public function update(?User $user, $aula = null): bool
    {
        return $this->verificarPermiso($user, 'aula.editar');
    }

    public function delete(?User $user, $aula = null): bool
    {
        return $this->verificarPermiso($user, 'aula.eliminar');
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

