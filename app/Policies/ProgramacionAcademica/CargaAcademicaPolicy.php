<?php

namespace App\Policies\ProgramacionAcademica;

use App\Models\User;
use App\Policies\Concerns\AuthorizesConPermiso;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización del módulo Sección - Unidad - Docente (`carga_academica`).
 *
 * Propósito: Evalúa permisos granulares `carga_academica.*` con compatibilidad hacia `seccion.*`
 * para mantener autorización multicapa coherente en Programación Académica.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial utilizando `HandlesAuthorization` y `AuthorizesConPermiso`.
 */
class CargaAcademicaPolicy
{
    use HandlesAuthorization;
    use AuthorizesConPermiso;

    public function viewAny(?User $user): bool
    {
        return $this->permiso($user, 'carga_academica.ver') || $this->permiso($user, 'seccion.ver');
    }

    public function view(?User $user, $cargaAcademica = null): bool
    {
        return $this->permiso($user, 'carga_academica.ver') || $this->permiso($user, 'seccion.ver');
    }

    public function create(?User $user): bool
    {
        return $this->permiso($user, 'carga_academica.crear') || $this->permiso($user, 'seccion.crear');
    }

    public function update(?User $user, $cargaAcademica = null): bool
    {
        return $this->permiso($user, 'carga_academica.editar') || $this->permiso($user, 'seccion.editar');
    }

    public function delete(?User $user, $cargaAcademica = null): bool
    {
        return $this->permiso($user, 'carga_academica.eliminar') || $this->permiso($user, 'seccion.eliminar');
    }
}
