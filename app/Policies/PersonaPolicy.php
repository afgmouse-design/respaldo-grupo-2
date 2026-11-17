<?php

namespace App\Policies;

use App\Models\Personas\Persona;
use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para la entidad Persona (tabla `persona`).
 *
 * Historial:
 *  - 2026-11-14: Refactorización para reutilizar `AuthorizesConPermiso`.
 */
class PersonaPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'estudiante.ver', 'registro_personas.ver');
    }

    public function view(?User $user, Persona $persona): bool
    {
        return $this->permiso($user, 'estudiante.ver', 'registro_personas.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'estudiante.crear', 'registro_personas.crear');
    }

    public function update(?User $user, Persona $persona): bool
    {
        return $this->permiso($user, 'estudiante.editar', 'registro_personas.editar');
    }

    public function delete(?User $user, Persona $persona): bool
    {
        return $this->permiso($user, 'estudiante.eliminar', 'registro_personas.eliminar');
    }
}
