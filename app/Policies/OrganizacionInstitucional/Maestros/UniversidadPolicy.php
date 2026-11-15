<?php

namespace App\Policies\OrganizacionInstitucional\Maestros;

use App\Models\OrganizacionInstitucional\Maestros\Universidad;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para el módulo de Universidad.
 *
 * Propósito: Centraliza las reglas de acceso y verificación de permisos para la
 * entidad Universidad según los estándares de seguridad multicapa (AGENTS.md §5 y §6).
 */
class UniversidadPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver la lista de universidades.
     */
    public function viewAny(?User $user): bool
    {
        return $this->verificarPermiso($user, 'universidad.ver', 'organizacion.ver');
    }

    /**
     * Determina si el usuario puede ver una universidad específica.
     */
    public function view(?User $user, $universidad = null): bool
    {
        return $this->verificarPermiso($user, 'universidad.ver', 'organizacion.ver');
    }

    /**
     * Determina si el usuario puede crear universidades.
     */
    public function create(?User $user): bool
    {
        return $this->verificarPermiso($user, 'universidad.crear', 'organizacion.crear');
    }

    /**
     * Determina si el usuario puede actualizar una universidad.
     */
    public function update(?User $user, $universidad = null): bool
    {
        return $this->verificarPermiso($user, 'universidad.editar', 'organizacion.editar');
    }

    /**
     * Determina si el usuario puede alternar estatus o eliminar una universidad.
     */
    public function delete(?User $user, $universidad = null): bool
    {
        return $this->verificarPermiso($user, 'universidad.eliminar', 'organizacion.eliminar');
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

