<?php

namespace Database\Seeders\SeguridadYAccesos\Concerns;

use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Permiso;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\Vista;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trait: SincronizaPermisosModulo.
 *
 * Propósito: Centraliza el registro idempotente de módulos (`modulo`), enlace opcional a vistas
 * (`vista`), catálogo de permisos (`permiso`) y vinculación automática al rol `Super Administrador`
 * (`rol_permiso`) evitando duplicación en los seeders por módulo.
 *
 * Historial:
 *  - 2026-11-14: Creación para migrar todos los `*PermisosSeeder` desde `key/activo` hacia
 *    `nombre_mod/clave_pmi/mod_id/vis_id/status_id` de `sogac_mio.sql`.
 */
trait SincronizaPermisosModulo
{
    /**
     * Sincroniza un módulo y sus permisos sobre las tablas oficiales de seguridad.
     *
     * @param string $nombreModulo
     * @param array<string, mixed> $atributosModulo
     * @param array<string, string|array{nombre:string, descripcion?:string, clave_vista?:string}> $permisos
     * @param string|null $claveVistaDefecto
     */
    protected function sincronizarPermisosDeModulo(
        string $nombreModulo,
        array $atributosModulo,
        array $permisos,
        ?string $claveVistaDefecto = null
    ): void {
        if (!Schema::hasTable('modulo') || !Schema::hasTable('permiso')) {
            $this->command?->warn('Tablas "modulo" o "permiso" no existen en la BD actual. Omitiendo permisos.');
            return;
        }

        $now = now();

        DB::transaction(function () use ($nombreModulo, $atributosModulo, $permisos, $claveVistaDefecto, $now): void {
            $modulo = Modulo::withTrashed()->firstOrCreate(
                ['nombre_mod' => $nombreModulo],
                [
                    'es_sistema_mod'  => (bool) ($atributosModulo['es_sistema_mod'] ?? false),
                    'descripcion_mod' => $atributosModulo['descripcion_mod'] ?? $nombreModulo,
                    'icono_mod'       => $atributosModulo['icono_mod'] ?? 'FolderOpen',
                    'orden_mod'       => (int) ($atributosModulo['orden_mod'] ?? 99),
                    'status_id'       => 1,
                ]
            );

            if ($modulo->trashed() || (int) $modulo->status_id !== 1) {
                $modulo->forceFill(['status_id' => 1, 'deleted_at' => null])->save();
            }

            $permisoIds = [];
            $hasVistaTable = Schema::hasTable('vista');

            foreach ($permisos as $clave => $meta) {
                $nombre = is_array($meta) ? $meta['nombre'] : $meta;
                $descripcion = is_array($meta) ? ($meta['descripcion'] ?? $nombre) : $nombre;
                $claveVista = is_array($meta) ? ($meta['clave_vista'] ?? $claveVistaDefecto) : $claveVistaDefecto;

                $visId = ($hasVistaTable && $claveVista)
                    ? Vista::where('clave_vis', $claveVista)->value('id_vis')
                    : null;

                $permiso = Permiso::withTrashed()->updateOrCreate(
                    ['clave_pmi' => $clave],
                    [
                        'mod_id'          => $modulo->id_mod,
                        'vis_id'          => $visId,
                        'nombre_pmi'      => $nombre,
                        'descripcion_pmi' => $descripcion,
                        'status_id'       => 1,
                        'deleted_at'      => null,
                    ]
                );

                $permisoIds[] = $permiso->id_pmi;
            }

            if (Schema::hasTable('rol') && Schema::hasTable('rol_permiso') && !empty($permisoIds)) {
                $superAdmin = Rol::withTrashed()
                    ->where('es_super_admin_rol', true)
                    ->first()
                    ?? Rol::withTrashed()->updateOrCreate(
                        ['nombre_rol' => 'Super Administrador'],
                        [
                            'descripcion_rol'         => 'Rol con acceso total al sistema SOGAC.',
                            'es_super_admin_rol'      => true,
                            'perfiles_permitidos_rol' => ['ESTUDIANTE', 'DOCENTE', 'ADMINISTRATIVO', 'AUTORIDAD'],
                            'status_id'               => 1,
                            'deleted_at'              => null,
                        ]
                    );

                foreach ($permisoIds as $pmiId) {
                    DB::table('rol_permiso')->updateOrInsert(
                        ['rol_id' => $superAdmin->id_rol, 'pmi_id' => $pmiId],
                        [
                            'status_id'  => 1,
                            'deleted_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        });
    }
}
