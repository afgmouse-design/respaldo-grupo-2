<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Trait: Autorización centralizada basada en permisos (`permiso.clave_pmi`).
 *
 * Propósito: Reemplazar la lógica repetida `verificarPermiso()` en las Policies del proyecto,
 * delegando en la jerarquía oficial del modelo `User` (`isSuperAdmin` -> `hasAnyPermission` -> `hasPermissionTo`).
 *
 * Historial:
 *  - 2026-11-14: Creación según estándar SOGAC (`AGENTS.md` §5 y §6) eliminando duplicación en Policies.
 */
trait AuthorizesConPermiso
{
    /**
     * Evalúa si el usuario autenticado tiene acceso por rol SuperAdmin o al menos uno de los permisos dados.
     */
    protected function permiso(?User $user, string ...$permisos): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasAnyPermission')) {
            return $user->hasAnyPermission($permisos);
        }

        if (method_exists($user, 'hasPermissionTo')) {
            foreach ($permisos as $clave) {
                if ($user->hasPermissionTo($clave)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
}
