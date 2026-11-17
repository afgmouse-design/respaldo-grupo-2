<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Tipo de Aula.
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class TipoAulaPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'tipo_aula.ver');
    }

    public function view(?User $user, $tipoAula = null): bool
    {
        return $this->permiso($user, 'tipo_aula.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'tipo_aula.crear');
    }

    public function update(?User $user, $tipoAula = null): bool
    {
        return $this->permiso($user, 'tipo_aula.editar');
    }

    public function delete(?User $user, $tipoAula = null): bool
    {
        return $this->permiso($user, 'tipo_aula.eliminar');
    }
}
