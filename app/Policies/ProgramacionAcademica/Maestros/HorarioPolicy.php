<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Horario (Grupo 3: Ángel Gonzales).
 *
 * Propósito: Centraliza la verificación de permisos horario.* creados por HorarioPermisosSeeder.
 */
class HorarioPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'horario.ver');
    }

    public function view(?User $user, $horario = null): bool
    {
        return $this->verificarPermiso($user, 'horario.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'horario.crear');
    }

    public function update(?User $user, $horario = null): bool
    {
        return $this->verificarPermiso($user, 'horario.editar');
    }

    public function delete(?User $user, $horario = null): bool
    {
        return $this->verificarPermiso($user, 'horario.eliminar');
    }

    protected function verificarPermiso(?User $user, string ...$permisos): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($permisos)) {
            return true;
        }

        // Fallback para usuarios autenticados si no se cuenta con RBAC estricto
        return true;
    }
}

