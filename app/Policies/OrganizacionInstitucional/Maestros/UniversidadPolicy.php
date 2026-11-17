<?php

namespace App\Policies\OrganizacionInstitucional\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para el módulo Universidad.
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class UniversidadPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'universidad.ver', 'organizacion.ver');
    }

    public function view(?User $user, $universidad = null): bool
    {
        return $this->permiso($user, 'universidad.ver', 'organizacion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'universidad.crear', 'organizacion.crear');
    }

    public function update(?User $user, $universidad = null): bool
    {
        return $this->permiso($user, 'universidad.editar', 'organizacion.editar');
    }

    public function delete(?User $user, $universidad = null): bool
    {
        return $this->permiso($user, 'universidad.eliminar', 'organizacion.eliminar');
    }
}
