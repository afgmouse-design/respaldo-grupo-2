<?php

namespace App\Policies\OrganizacionInstitucional\Maestros;

use App\Models\OrganizacionInstitucional\Maestros\Sede;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para el módulo de Sede Universitaria.
 *
 * Propósito: Centraliza las reglas de acceso y verificación de permisos para la
 * entidad Sede según los estándares de seguridad multicapa (AGENTS.md §5 y §6).
 */
class SedePolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver la lista de sedes.
     */
    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'sede.ver', 'organizacion.ver');
    }

    /**
     * Determina si el usuario puede ver una sede específica.
     */
    public function view(?User $user, $sede = null): bool
    {
        return $this->verificarPermiso($user, 'sede.ver', 'organizacion.ver');
    }

    /**
     * Determina si el usuario puede crear sedes.
     */
    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'sede.crear', 'organizacion.crear');
    }

    /**
     * Determina si el usuario puede actualizar una sede.
     */
    public function update(?User $user, $sede = null): bool
    {
        return $this->verificarPermiso($user, 'sede.editar', 'organizacion.editar');
    }

    /**
     * Determina si el usuario puede alternar estatus o eliminar una sede.
     */
    public function delete(?User $user, $sede = null): bool
    {
        return $this->verificarPermiso($user, 'sede.eliminar', 'organizacion.eliminar');
    }

    /**
     * Helper para verificar permisos con soporte para roles o fallback autenticado.
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

        // Si el usuario está autenticado y no hay motor Spatie configurado aún, autoriza por defecto
        return true;
    }
}

