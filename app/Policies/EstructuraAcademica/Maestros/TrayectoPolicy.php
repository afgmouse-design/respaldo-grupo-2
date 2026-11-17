<?php

namespace App\Policies\EstructuraAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para Trayecto (`trayecto`).
 *
 * Historial:
 *  - 2026-11-14: Alineado al estándar de permisos `trayecto.*` vía `AuthorizesConPermiso`.
 */
class TrayectoPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'trayecto.ver');
    }

    public function view(?User $user, $model = null): bool
    {
        return $this->permiso($user, 'trayecto.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'trayecto.crear');
    }

    public function update(?User $user, $model = null): bool
    {
        return $this->permiso($user, 'trayecto.editar');
    }

    public function delete(?User $user, $model = null): bool
    {
        return $this->permiso($user, 'trayecto.eliminar');
    }
}
