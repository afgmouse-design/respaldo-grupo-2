<?php

namespace App\Policies\EstructuraAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para Escala de Medición (`escala_medicion`).
 *
 * Historial:
 *  - 2026-11-14: Alineado al estándar de permisos `escala_medicion.*` vía `AuthorizesConPermiso`.
 */
class EscalaMedicionPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'escala_medicion.ver');
    }

    public function view(?User $user, $model = null): bool
    {
        return $this->permiso($user, 'escala_medicion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'escala_medicion.crear');
    }

    public function update(?User $user, $model = null): bool
    {
        return $this->permiso($user, 'escala_medicion.editar');
    }

    public function delete(?User $user, $model = null): bool
    {
        return $this->permiso($user, 'escala_medicion.eliminar');
    }
}
