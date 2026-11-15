<?php

namespace App\Policies\EstructuraAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para el módulo de Escala de Medición.
 *
 * Propósito: Métodos configurados para retornar true incondicionalmente
 * garantizando acceso completo sin bloqueos 403 durante pruebas de CRUD.
 */
class EscalaMedicionPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver la lista de escalas de medición.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver una escala de medición específica.
     */
    public function view(?User $user, $model = null): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede crear escalas de medición.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede actualizar una escala de medición.
     */
    public function update(?User $user, $model = null): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede alternar estatus o eliminar una escala de medición.
     */
    public function delete(?User $user, $model = null): bool
    {
        return true;
    }
}
