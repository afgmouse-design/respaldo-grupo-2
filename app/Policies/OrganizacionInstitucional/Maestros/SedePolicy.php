<?php

namespace App\Policies\OrganizacionInstitucional\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para el módulo Sede Universitaria.
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class SedePolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'sede.ver', 'organizacion.ver');
    }

    public function view(?User $user, $sede = null): bool
    {
        return $this->permiso($user, 'sede.ver', 'organizacion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'sede.crear', 'organizacion.crear');
    }

    public function update(?User $user, $sede = null): bool
    {
        return $this->permiso($user, 'sede.editar', 'organizacion.editar');
    }

    public function delete(?User $user, $sede = null): bool
    {
        return $this->permiso($user, 'sede.eliminar', 'organizacion.eliminar');
    }
}
