<?php

namespace Database\Seeders\SeguridadYAccesos\Usuario;

use App\Models\Personas\Perfil;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaPerfil;
use App\Models\Seguridad\Permiso;
use App\Models\Seguridad\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: Usuario Administrador Inicial y Contexto RBAC (`users`, `persona`, `rol`, `rol_usuario_contexto`).
 *
 * Propósito: Crea de forma idempotente el usuario administrador estándar usando `username`
 * (sin columna `name`), su ficha `persona` (`id_per`), el rol `Super Administrador` con todos los
 * permisos sincronizados y su asignación global en `rol_usuario_contexto`.
 *
 * Historial:
 *  - 2026-11-14: Reescrito usando `username`, `status_id = 1`, `estatus = true`, enlace a
 *    `persona(id_per)`/`persona_perfil(id_pep)` y asignación idempotente de `rol_usuario_contexto`.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $user = User::withTrashed()->updateOrCreate(
                ['email' => 'admin@gmail.com'],
                [
                    'username'   => 'admin',
                    'password'   => Hash::make('admin123'),
                    'estatus'    => true,
                    'status_id'  => 1,
                    'deleted_at' => null,
                ]
            );

            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => $now])->save();
            }

            $persona = Persona::withTrashed()->updateOrCreate(
                ['cedula_per' => 'V00000000'],
                [
                    'use_id'           => $user->id,
                    'nacionalidad_per' => 'V',
                    'documento_per'    => '00000000',
                    'nombres_per'      => 'Administrador',
                    'apellidos_per'    => 'SOGAC',
                    'correo_per'       => 'admin@gmail.com',
                    'status_id'        => 1,
                    'deleted_at'       => null,
                ]
            );

            $perfilAdmin = Perfil::where('codigo_prf', 'ADMINISTRATIVO')->first();
            if ($perfilAdmin) {
                PersonaPerfil::withTrashed()->updateOrCreate(
                    ['per_id' => $persona->id_per, 'prf_id' => $perfilAdmin->id_prf],
                    ['status_id' => 1, 'deleted_at' => null]
                );
            }

            $superAdmin = Rol::withTrashed()->updateOrCreate(
                ['nombre_rol' => 'Super Administrador'],
                [
                    'descripcion_rol'         => 'Rol con acceso completo a todos los módulos del sistema SOGAC.',
                    'es_super_admin_rol'      => true,
                    'perfiles_permitidos_rol' => ['ESTUDIANTE', 'DOCENTE', 'ADMINISTRATIVO', 'AUTORIDAD'],
                    'status_id'               => 1,
                    'deleted_at'              => null,
                ]
            );

            $permisoIds = Permiso::where('status_id', 1)->whereNull('deleted_at')->pluck('id_pmi');
            foreach ($permisoIds as $pmiId) {
                DB::table('rol_permiso')->updateOrInsert(
                    ['rol_id' => $superAdmin->id_rol, 'pmi_id' => $pmiId],
                    ['status_id' => 1, 'deleted_at' => null, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $contextoExistente = DB::table('rol_usuario_contexto')
                ->where('use_id', $user->id)
                ->where('rol_id', $superAdmin->id_rol)
                ->whereNull('prg_id')
                ->whereNull('sed_id')
                ->first();

            if ($contextoExistente) {
                DB::table('rol_usuario_contexto')
                    ->where('id_ruc', $contextoExistente->id_ruc)
                    ->update(['status_id' => 1, 'deleted_at' => null, 'updated_at' => $now]);
            } else {
                DB::table('rol_usuario_contexto')->insert([
                    'use_id'     => $user->id,
                    'rol_id'     => $superAdmin->id_rol,
                    'prg_id'     => null,
                    'sed_id'     => null,
                    'status_id'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
