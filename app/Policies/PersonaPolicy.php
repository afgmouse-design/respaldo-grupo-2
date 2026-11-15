<?php

namespace App\Policies;

use App\Models\Personas\Persona;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'estudiante.ver', 'registro_personas.ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Persona $persona): bool
    {
        return $this->verificarPermiso($user, 'estudiante.ver', 'registro_personas.ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'estudiante.crear', 'registro_personas.crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Persona $persona): bool
    {
        return $this->verificarPermiso($user, 'estudiante.editar', 'registro_personas.editar');
    }

    /**
     * Determine whether the user can delete/toggle the model.
     */
    public function delete(?User $user, Persona $persona): bool
    {
        return $this->verificarPermiso($user, 'estudiante.eliminar', 'registro_personas.eliminar');
    }

    /**
     * Helper para verificar permisos con soporte para SuperAdmin o fallback
     */
    protected function verificarPermiso(?User $user, string ...$permisos): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasPermissionTo')) {
            foreach ($permisos as $permiso) {
                if ($user->hasPermissionTo($permiso)) {
                    return true;
                }
            }
            return false;
        }

        // Si el usuario está autenticado y no hay permisos específicos asignados, permitir acceso
        return true;
    }
}
