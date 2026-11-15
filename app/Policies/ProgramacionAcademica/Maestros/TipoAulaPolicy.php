<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Tipo de Aula.
 * Propósito: centraliza la verificación de los permisos tipo_aula.* creados por TipoAulaPermisosSeeder.
 * Sigue el mismo patrón que SedePolicy (superadmin → permiso directo → fallback autenticado).
 */
class TipoAulaPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'tipo_aula.ver');
    }

    public function view(?User $user, $tipoAula = null): bool
    {
        return $this->verificarPermiso($user, 'tipo_aula.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'tipo_aula.crear');
    }

    public function update(?User $user, $tipoAula = null): bool
    {
        return $this->verificarPermiso($user, 'tipo_aula.editar');
    }

    public function delete(?User $user, $tipoAula = null): bool
    {
        return $this->verificarPermiso($user, 'tipo_aula.eliminar');
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

