<?php

namespace App\Policies\ProgramacionAcademica\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Oferta Sede-Programa (tabla `sede_programa`).
 *
 * Propósito: Centraliza permisos `sede_programa.*` (y alias `sede_pnf.*`).
 *
 * Historial:
 *  - 2026-11-14: Creación sustituyendo a `SedePnfPolicy` según `sogac_mio.sql` usando `AuthorizesConPermiso`.
 */
class SedeProgramaPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'sede_programa.ver', 'sede_pnf.ver');
    }

    public function view(?User $user, $sedePrograma = null): bool
    {
        return $this->permiso($user, 'sede_programa.ver', 'sede_pnf.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'sede_programa.crear', 'sede_pnf.crear');
    }

    public function update(?User $user, $sedePrograma = null): bool
    {
        return $this->permiso($user, 'sede_programa.editar', 'sede_pnf.editar');
    }

    public function delete(?User $user, $sedePrograma = null): bool
    {
        return $this->permiso($user, 'sede_programa.eliminar', 'sede_pnf.eliminar');
    }
}
