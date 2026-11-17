<?php

namespace App\Policies;

use App\Models\Personas\Perfil;
use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para el catálogo de Perfiles (`perfil`).
 *
 * Historial:
 *  - 2026-11-14: Apuntado al modelo oficial `App\Models\Personas\Perfil` y al trait `AuthorizesConPermiso`.
 */
class PerfilPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'perfil.ver');
    }

    public function view(?User $user, ?Perfil $perfil = null): bool
    {
        return $this->permiso($user, 'perfil.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'perfil.crear');
    }

    public function update(?User $user, ?Perfil $perfil = null): bool
    {
        return $this->permiso($user, 'perfil.editar');
    }

    public function delete(?User $user, ?Perfil $perfil = null): bool
    {
        return $this->permiso($user, 'perfil.eliminar');
    }
}
