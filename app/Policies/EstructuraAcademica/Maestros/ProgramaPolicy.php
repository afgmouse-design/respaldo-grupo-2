<?php

namespace App\Policies\EstructuraAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Programa Académico (tabla `programa`).
 *
 * Propósito: Centraliza permisos `programa.*` (con compatibilidad de alias `pnf.*`).
 *
 * Historial:
 *  - 2026-11-14: Creación para la entidad oficial `Programa` de `sogac_mio.sql`.
 */
class ProgramaPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'programa.ver', 'pnf.ver');
    }

    public function view(?User $user, $programa = null): bool
    {
        return $this->permiso($user, 'programa.ver', 'pnf.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'programa.crear', 'pnf.crear');
    }

    public function update(?User $user, $programa = null): bool
    {
        return $this->permiso($user, 'programa.editar', 'pnf.editar');
    }

    public function delete(?User $user, $programa = null): bool
    {
        return $this->permiso($user, 'programa.eliminar', 'pnf.eliminar');
    }
}
