<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Sede PNF (oferta académica por sede).
 * Centraliza los permisos sede_pnf.ver / crear / editar / eliminar.
 * Nota: sigue el patrón de las demás policies del proyecto (verificarPermiso), ya que el trait
 * AuthorizesConPermiso aún no existe en el código base.
 */
class SedePnfPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'sede_pnf.ver');
    }

    public function view(?User $user, $sedePnf = null): bool
    {
        return $this->verificarPermiso($user, 'sede_pnf.ver');
    }

    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'sede_pnf.crear');
    }

    public function update(?User $user, $sedePnf = null): bool
    {
        return $this->verificarPermiso($user, 'sede_pnf.editar');
    }

    public function delete(?User $user, $sedePnf = null): bool
    {
        return $this->verificarPermiso($user, 'sede_pnf.eliminar');
    }

    /**
     * Superadmin pasa siempre; si el usuario soporta hasPermissionTo se evalúan los permisos.
     */
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

