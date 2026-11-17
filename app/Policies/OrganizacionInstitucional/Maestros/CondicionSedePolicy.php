<?php

namespace App\Policies\OrganizacionInstitucional\Maestros;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del maestro Condición de Sede (tabla `condicion_sede`).
 *
 * Propósito: Centraliza permisos `condicion_sede.*` (y alias `condicion.*`) para CRUD de condiciones de sede.
 *
 * Historial:
 *  - 2026-11-14: Creación sustituyendo a `CondicionPolicy` alineada con `condicion_sede` en `sogac_mio.sql`.
 */
class CondicionSedePolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'condicion_sede.ver', 'condicion.ver');
    }

    public function view(?User $user, $condicionSede = null): bool
    {
        return $this->permiso($user, 'condicion_sede.ver', 'condicion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'condicion_sede.crear', 'condicion.crear');
    }

    public function update(?User $user, $condicionSede = null): bool
    {
        return $this->permiso($user, 'condicion_sede.editar', 'condicion.editar');
    }

    public function delete(?User $user, $condicionSede = null): bool
    {
        return $this->permiso($user, 'condicion_sede.eliminar', 'condicion.eliminar');
    }
}
