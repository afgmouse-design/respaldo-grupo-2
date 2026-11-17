<?php

namespace App\Models\Concerns;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Trait reutilizable para estandarizar la baja lógica (soft delete) en interfaz
 * conservando el registro en PostgreSQL (`status_id = 2` + `deleted_at`), así como
 * la liberación segura de claves únicas en registros previamente eliminados.
 *
 * Historial:
 * - 2026-11-16: Creación del trait para unificar eliminación lógica en interfaz,
 *   cascadas territoriales/académicas y archivado de valores UNIQUE eliminados.
 */
trait HandlesSoftDeleteWithStatus
{
    /**
     * Archiva un valor único (`-d{id}`) cuando pertenece a un registro ya eliminado lógicamente,
     * permitiendo reutilizar el código o nombre sin violar restricciones UNIQUE de PostgreSQL.
     * Si existe un registro activo (deleted_at IS NULL) con ese valor, lanza DomainException.
     */
    public static function liberarValorUnicoEliminado(
        string $table,
        string $pkColumn,
        string $column,
        string $value,
        ?int $ignoreId = null,
        int $maxLength = 255,
        ?string $conflictMessage = null,
        bool $caseInsensitive = false
    ): void {
        $query = DB::table($table)->select([$pkColumn, $column, 'deleted_at']);

        if ($caseInsensitive) {
            $query->whereRaw("LOWER({$column}) = LOWER(?)", [$value]);
        } else {
            $query->where($column, $value);
        }

        $coincidencias = $query->get();

        foreach ($coincidencias as $registro) {
            $pk = (int) $registro->{$pkColumn};

            if ($ignoreId !== null && $pk === $ignoreId) {
                continue;
            }

            if ($registro->deleted_at === null) {
                throw new DomainException(
                    $conflictMessage ?? "Ya existe un registro activo con el valor '{$value}'."
                );
            }

            $sufijo = "-d{$pk}";
            $longitudBase = max(0, $maxLength - mb_strlen($sufijo));
            $valorArchivado = mb_substr((string) $registro->{$column}, 0, $longitudBase) . $sufijo;

            DB::table($table)
                ->where($pkColumn, $pk)
                ->update([$column => $valorArchivado]);
        }
    }

    /**
     * Ejecuta baja lógica segura sobre un modelo Eloquent (`status_id = 2` y `deleted_at = now()`),
     * garantizando que nunca se ejecute un DELETE físico aunque el modelo no declare `SoftDeletes`.
     */
    public static function ejecutarBajaLogicaModelo(
        Model $registro,
        ?int $userId = null,
        int $statusInactivo = 2
    ): void {
        $usuarioId = $userId ?? auth()->id();
        $now = now();

        if (in_array(SoftDeletes::class, class_uses_recursive($registro), true)) {
            $registro->forceFill([
                'status_id' => $statusInactivo,
                'updated_id' => $usuarioId,
            ])->saveQuietly();

            $registro->delete();
            return;
        }

        DB::table($registro->getTable())
            ->where($registro->getKeyName(), $registro->getKey())
            ->whereNull('deleted_at')
            ->update([
                'status_id' => $statusInactivo,
                'updated_id' => $usuarioId,
                'updated_at' => $now,
                'deleted_at' => $now,
            ]);

        $registro->forceFill([
            'status_id' => $statusInactivo,
            'updated_id' => $usuarioId,
            'updated_at' => $now,
            'deleted_at' => $now,
        ]);
    }

    /**
     * Ejecuta la baja lógica sobre un registro padre (disparando auditoría de eliminación)
     * y propaga `status_id = 2` + `deleted_at` en cascada a los niveles hijos configurados.
     *
     * @param array<int, array{table: string, pk: string, fk: string, has_status?: bool, has_updated_id?: bool}> $nivelesHijos
     */
    public static function ejecutarBajaLogicaConHijos(
        Model $registro,
        array $nivelesHijos = [],
        ?int $userId = null,
        int $statusInactivo = 2
    ): void {
        $rootId = (int) $registro->getKey();
        static::ejecutarBajaLogicaModelo($registro, $userId, $statusInactivo);

        if (! empty($nivelesHijos)) {
            static::registrarBajaLogicaJerarquica($nivelesHijos, [$rootId], $userId, $statusInactivo);
        }
    }

    /**
     * Aplica baja lógica (`deleted_at = now()`, `updated_at = now()` y opcionalmente `status_id` / `updated_id`)
     * en cascada descendente siguiendo una cadena de niveles [['table', 'pk', 'fk', ...], ...].
     *
     * @param array<int, array{table: string, pk: string, fk: string, has_status?: bool, has_updated_id?: bool}> $niveles
     */
    protected static function registrarBajaLogicaJerarquica(
        array $niveles,
        int|array $rootIds,
        ?int $userId = null,
        int $statusInactivo = 2
    ): void {
        $currentIds = array_values(array_unique(array_map('intval', (array) $rootIds)));
        if (empty($currentIds)) {
            return;
        }

        $now = now();
        $usuarioId = $userId ?? auth()->id();

        foreach ($niveles as $nivel) {
            $matchedIds = DB::table($nivel['table'])
                ->whereIn($nivel['fk'], $currentIds)
                ->whereNull('deleted_at')
                ->pluck($nivel['pk'])
                ->all();

            if (empty($matchedIds)) {
                break;
            }

            $payload = [
                'updated_at' => $now,
                'deleted_at' => $now,
            ];

            if ($nivel['has_status'] ?? true) {
                $payload['status_id'] = $statusInactivo;
            }

            if ($nivel['has_updated_id'] ?? true) {
                $payload['updated_id'] = $usuarioId;
            }

            DB::table($nivel['table'])
                ->whereIn($nivel['pk'], $matchedIds)
                ->whereNull('deleted_at')
                ->update($payload);

            $currentIds = $matchedIds;
        }
    }
}
