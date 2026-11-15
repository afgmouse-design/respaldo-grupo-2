<?php

namespace App\Policies\EstructuraAcademica\Maestros;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy: Autorización para Trayecto.
 */
class TrayectoPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, $model = null): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, $model = null): bool
    {
        return true;
    }

    public function delete(?User $user, $model = null): bool
    {
        return true;
    }
}

