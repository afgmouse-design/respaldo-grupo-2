<?php

namespace App\Policies\OrganizacionInstitucional\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Condición.
 * Centraliza la verificación de los permisos condicion.* creados por CondicionPermisosSeeder
 * (mismo patrón que SedePolicy).
 */
class CondicionPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'condicion.ver');
    }

    public function view(?User $user, $condicion = null): bool
    {
        return $this->verificarPermiso($user, 'condicion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'condicion.crear');
    }

    public function update(?User $user, $condicion = null): bool
    {
        return $this->verificarPermiso($user, 'condicion.editar');
    }

    public function delete(?User $user, $condicion = null): bool
    {
        return $this->verificarPermiso($user, 'condicion.eliminar');
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

