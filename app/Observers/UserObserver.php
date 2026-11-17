<?php

namespace App\Observers;

use App\Models\User;

/**
 * Observer para eventos del ciclo de vida del modelo `User`.
 *
 * Historial:
 *  - 2026-11-14: Eliminada la referencia rota a `App\Models\Product` para alinear el Observer con `User`.
 */
class UserObserver
{
    /**
     * Evento tras crear un usuario.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Evento tras actualizar un usuario.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Evento tras eliminación lógica (`SoftDeletes`) de un usuario.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Evento tras restauración de un usuario.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Evento tras eliminación definitiva de un usuario.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
