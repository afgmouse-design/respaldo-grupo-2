<?php

namespace App\Models\Academico;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Año Académico (tabla `ano_academico`).
 *
 * Propósito: Macro-ciclo institucional que agrupa lapsos académicos (`lapso_academico.ana_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a convenciones de `sogac_mio.sql` (`id_ana`, `nombre_ana`,
 *    `fecha_inicio_ana`, `fecha_fin_ana`, `status_id`, `SoftDeletes`, auditoría `bigint`,
 *    sin columnas duplicadas `*_ano` ni columna booleana `status`).
 */
class AnoAcademico extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'ano_academico';
    protected $primaryKey = 'id_ana';
    protected $moduloAuditoria = 'AnoAcademico';

    protected $fillable = [
        'nombre_ana',
        'fecha_inicio_ana',
        'fecha_fin_ana',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_ana'           => 'integer',
        'fecha_inicio_ana' => 'date',
        'fecha_fin_ana'    => 'date',
        'status_id'        => 'integer',
    ];

    public function lapsosAcademicos(): HasMany
    {
        return $this->hasMany(LapsoAcademico::class, 'ana_id', 'id_ana');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(?string $busqueda = null, int $porPagina = 10, mixed $status = null): LengthAwarePaginator
    {
        $statusId = static::resolverFiltroStatusId($status);

        $query = DB::table('ano_academico')
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($busqueda)) {
            $query->where('nombre_ana', 'ilike', "%{$busqueda}%");
        }

        return $query->orderBy('fecha_inicio_ana', 'desc')
            ->orderBy('id_ana', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public static function registrar(array $datos): ?object
    {
        $now = now();
        $registro = [
            'nombre_ana'       => $datos['nombre_ana'] ?? $datos['nombre_ano'] ?? '',
            'fecha_inicio_ana' => $datos['fecha_inicio_ana'] ?? $datos['fecha_inicio_ano'] ?? null,
            'fecha_fin_ana'    => $datos['fecha_fin_ana'] ?? $datos['fecha_fin_ano'] ?? null,
            'status_id'        => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_id'       => auth()->id(),
            'created_at'       => $now,
            'updated_at'       => $now,
        ];

        $id = DB::table('ano_academico')->insertGetId($registro, 'id_ana');

        return self::obtenerPorId($id);
    }

    public static function actualizar(int|string $id, array $datos): ?object
    {
        $campos = ['updated_at' => now()];

        if (array_key_exists('nombre_ana', $datos) || array_key_exists('nombre_ano', $datos)) {
            $campos['nombre_ana'] = $datos['nombre_ana'] ?? $datos['nombre_ano'];
        }
        if (array_key_exists('fecha_inicio_ana', $datos) || array_key_exists('fecha_inicio_ano', $datos)) {
            $campos['fecha_inicio_ana'] = $datos['fecha_inicio_ana'] ?? $datos['fecha_inicio_ano'];
        }
        if (array_key_exists('fecha_fin_ana', $datos) || array_key_exists('fecha_fin_ano', $datos)) {
            $campos['fecha_fin_ana'] = $datos['fecha_fin_ana'] ?? $datos['fecha_fin_ano'];
        }
        if (array_key_exists('status_id', $datos)) {
            $campos['status_id'] = (int) $datos['status_id'];
        } elseif (array_key_exists('status', $datos)) {
            $campos['status_id'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN)
                ? self::STATUS_ACTIVO
                : self::STATUS_INACTIVO;
        }
        if (auth()->check()) {
            $campos['updated_id'] = auth()->id();
        }

        DB::table('ano_academico')
            ->where('id_ana', (int) $id)
            ->whereNull('deleted_at')
            ->update($campos);

        return self::obtenerPorId($id);
    }

    public static function obtenerPorId(int|string $id): ?object
    {
        return DB::table('ano_academico')
            ->where('id_ana', (int) $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Alterna el estatus manteniendo la regla de único año académico activo y cascada hacia lapsos/cohortes.
     */
    public static function alternarEstatus(int|string $id, mixed $nuevoEstatus = null): bool
    {
        $ano = DB::table('ano_academico')->where('id_ana', (int) $id)->whereNull('deleted_at')->first();
        if (! $ano) {
            throw new \DomainException('El año académico solicitado no existe.');
        }

        if (is_null($nuevoEstatus)) {
            $activar = ! static::esStatusActivo($ano);
        } elseif ($nuevoEstatus === self::STATUS_ACTIVO || $nuevoEstatus === true || $nuevoEstatus === '1') {
            $activar = true;
        } else {
            $activar = false;
        }

        if ($activar) {
            $otroActivo = DB::table('ano_academico')
                ->where('id_ana', '!=', (int) $id)
                ->where('status_id', self::STATUS_ACTIVO)
                ->whereNull('deleted_at')
                ->exists();

            if ($otroActivo) {
                throw new \DomainException('Ya existe otro Año Académico activo en el sistema. Desactive el año en curso antes de activar otro.');
            }
        }

        $statusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;

        return DB::transaction(function () use ($id, $statusId, $activar) {
            $now = now();
            $userId = auth()->id();

            DB::table('ano_academico')
                ->where('id_ana', (int) $id)
                ->update([
                    'status_id'  => $statusId,
                    'updated_id' => $userId,
                    'updated_at' => $now,
                ]);

            if (! $activar) {
                $lapsoIds = DB::table('lapso_academico')
                    ->where('ana_id', (int) $id)
                    ->pluck('id_laa');

                if ($lapsoIds->isNotEmpty()) {
                    DB::table('lapso_academico')
                        ->whereIn('id_laa', $lapsoIds)
                        ->update([
                            'status_id'  => self::STATUS_INACTIVO,
                            'updated_id' => $userId,
                            'updated_at' => $now,
                        ]);

                    $cohorteIds = DB::table('cohorte')
                        ->whereIn('laa_id', $lapsoIds)
                        ->pluck('id_coh');

                    if ($cohorteIds->isNotEmpty()) {
                        DB::table('cohorte')
                            ->whereIn('id_coh', $cohorteIds)
                            ->update([
                                'status_id'  => self::STATUS_INACTIVO,
                                'updated_id' => $userId,
                                'updated_at' => $now,
                            ]);

                        DB::table('cohorte_sede')
                            ->whereIn('coh_id', $cohorteIds)
                            ->update([
                                'status_id'  => self::STATUS_INACTIVO,
                                'updated_id' => $userId,
                                'updated_at' => $now,
                            ]);
                    }
                }
            }

            return $activar;
        });
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('ano_academico')
            ->select(['id_ana', 'nombre_ana', 'fecha_inicio_ana', 'fecha_fin_ana'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_ana', 'desc')
            ->get();
    }
}
