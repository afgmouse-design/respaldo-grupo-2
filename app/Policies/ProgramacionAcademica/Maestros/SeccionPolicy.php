<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Sección (Programación Académica).
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class SeccionPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'seccion.ver');
    }

    public function view(?User $user, $seccion = null): bool
    {
        return $this->permiso($user, 'seccion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'seccion.crear');
    }

    public function update(?User $user, $seccion = null): bool
    {
        return $this->permiso($user, 'seccion.editar');
    }

    public function delete(?User $user, $seccion = null): bool
    {
        return $this->permiso($user, 'seccion.eliminar');
    }
}
