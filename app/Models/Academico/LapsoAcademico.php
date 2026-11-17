<?php

namespace App\Models\Academico;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\OrganizacionInstitucional\Maestros\Universidad;
use App\Models\ProgramacionAcademica\Maestros\Cohorte;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Lapso Académico (tabla `lapso_academico`).
 *
 * Propósito: Gestiona los lapsos y períodos lectivos del sistema vinculados a un Año Académico (`ana_id`)
 * y un Tipo de Lapso (`tla_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`id_laa`, `tla_id`, `ana_id`, `codigo_laa`, `nombre_laa`,
 *    `fecha_inicio_laa`, `fecha_fin_laa`, `condicion_laa`, `cerrado_laa`, `habilitar_nota_laa`,
 *    `fase_operativa_laa`, `status_id`, `SoftDeletes`, sin columnas legacy `*_la` ni `status` bool).
 *  - 2026-11-16: Se añade `liberarCodigoSiEliminado()` dentro de transacción en `registrarLapso()` y
 *    `actualizarLapso()` para evitar `SQLSTATE[23505]` (`lapso_academico_codigo_laa_unique`) al
 *    reutilizar códigos de lapsos previamente eliminados con `deleted_at`.
 */
class LapsoAcademico extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'lapso_academico';
    protected $primaryKey = 'id_laa';
    protected $moduloAuditoria = 'LapsoAcademico';

    protected $fillable = [
        'tla_id',
        'uni_id',
        'ana_id',
        'codigo_laa',
        'nombre_laa',
        'fecha_inicio_laa',
        'fecha_fin_laa',
        'condicion_laa',
        'cerrado_laa',
        'habilitar_nota_laa',
        'fase_operativa_laa',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_laa'           => 'integer',
        'tla_id'           => 'integer',
        'uni_id'           => 'integer',
        'ana_id'           => 'integer',
        'fecha_inicio_laa' => 'date',
        'fecha_fin_laa'    => 'date',
        'status_id'        => 'integer',
    ];

    public function tipoLapso(): BelongsTo
    {
        return $this->belongsTo(TipoLapso::class, 'tla_id', 'id_tla');
    }

    public function anoAcademico(): BelongsTo
    {
        return $this->belongsTo(AnoAcademico::class, 'ana_id', 'id_ana');
    }

    public function universidad(): BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'uni_id', 'id_uni');
    }

    public function cortes(): HasMany
    {
        return $this->hasMany(CorteLapso::class, 'laa_id', 'id_laa');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoCalendario::class, 'laa_id', 'id_laa');
    }

    public function cohortes(): HasMany
    {
        return $this->hasMany(Cohorte::class, 'laa_id', 'id_laa');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'laa_id', 'id_laa');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('lapso_academico')
            ->select(['id_laa', 'codigo_laa', 'nombre_laa', 'fecha_inicio_laa', 'fecha_fin_laa', 'ana_id', 'tla_id'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('fecha_inicio_laa', 'desc')
            ->orderBy('id_laa', 'desc')
            ->get();
    }

    public static function obtenerPaginados(
        ?string $busqueda = null,
        mixed $status = null,
        int $porPagina = 10,
        mixed $anaId = null
    ): LengthAwarePaginator {
        $statusId = static::resolverFiltroStatusId($status);

        $query = DB::table('lapso_academico')
            ->leftJoin('ano_academico', 'lapso_academico.ana_id', '=', 'ano_academico.id_ana')
            ->leftJoin('tipo_lapso', 'lapso_academico.tla_id', '=', 'tipo_lapso.id_tla')
            ->select([
                'lapso_academico.id_laa',
                'lapso_academico.tla_id',
                'lapso_academico.uni_id',
                'lapso_academico.ana_id',
                'lapso_academico.codigo_laa',
                'lapso_academico.nombre_laa',
                'lapso_academico.fecha_inicio_laa',
                'lapso_academico.fecha_fin_laa',
                'lapso_academico.condicion_laa',
                'lapso_academico.cerrado_laa',
                'lapso_academico.habilitar_nota_laa',
                'lapso_academico.fase_operativa_laa',
                'lapso_academico.status_id',
                'ano_academico.nombre_ana',
                'ano_academico.status_id as ano_status_id',
                'tipo_lapso.nombre_tla',
                'tipo_lapso.codigo_tla',
            ])
            ->whereNull('lapso_academico.deleted_at')
            ->where(function ($q) {
                $q->whereNull('ano_academico.deleted_at')
                    ->orWhereNull('lapso_academico.ana_id');
            })
            ->when($statusId !== null, fn ($q) => $q->where('lapso_academico.status_id', $statusId));

        if (! empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('lapso_academico.nombre_laa', 'ilike', "%{$busqueda}%")
                    ->orWhere('lapso_academico.codigo_laa', 'ilike', "%{$busqueda}%");
            });
        }

        if (! empty($anaId) && $anaId !== 'all') {
            $query->where('lapso_academico.ana_id', (int) $anaId);
        }

        return $query->orderBy('lapso_academico.fecha_inicio_laa', 'desc')
            ->orderBy('lapso_academico.id_laa', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /**
     * Libera el código de un lapso previamente eliminado con soft delete para respetar
     * la restricción única global de PostgreSQL (`lapso_academico_codigo_laa_unique`) sin alterar el esquema.
     *
     * @throws \DomainException Si existe un lapso activo/no eliminado con el mismo código.
     */
    private static function liberarCodigoSiEliminado(string $codigo, ?int $ignoreId = null): void
    {
        $existente = DB::table('lapso_academico')
            ->select(['id_laa', 'deleted_at'])
            ->where('codigo_laa', $codigo)
            ->when($ignoreId !== null, fn ($q) => $q->where('id_laa', '!=', $ignoreId))
            ->first();

        if (! $existente) {
            return;
        }

        if ($existente->deleted_at === null) {
            throw new \DomainException("Ya existe un lapso académico registrado con el código '{$codigo}'.");
        }

        $sufijo = '-d' . $existente->id_laa;
        $baseLen = max(1, 20 - strlen($sufijo));
        $codigoArchivado = substr($codigo, 0, $baseLen) . $sufijo;

        if (DB::table('lapso_academico')->where('codigo_laa', $codigoArchivado)->where('id_laa', '!=', $existente->id_laa)->exists()) {
            $codigoArchivado = substr('d' . $existente->id_laa . '-' . bin2hex(random_bytes(4)), 0, 20);
        }

        DB::table('lapso_academico')
            ->where('id_laa', $existente->id_laa)
            ->update(['codigo_laa' => $codigoArchivado]);
    }

    public static function registrarLapso(array $datos): ?object
    {
        $now = now();
        $tlaId = $datos['tla_id'] ?? $datos['tpd_id'] ?? DB::table('tipo_lapso')->whereNull('deleted_at')->value('id_tla');
        $nombre = trim((string) ($datos['nombre_laa'] ?? $datos['nombre_la'] ?? ''));
        $codigo = substr(trim((string) ($datos['codigo_laa'] ?? '')), 0, 20);

        $id = DB::transaction(function () use ($datos, $now, $tlaId, $nombre, $codigo) {
            if ($codigo === '') {
                $base = substr(preg_replace('/[^A-Za-z0-9\-]/', '', $nombre), 0, 14) ?: 'LAPSO';
                do {
                    $codigo = substr(strtoupper($base . '-' . random_int(100, 999)), 0, 20);
                } while (DB::table('lapso_academico')->where('codigo_laa', $codigo)->exists());
            } else {
                self::liberarCodigoSiEliminado($codigo);
            }

            $registro = [
                'tla_id'             => (int) $tlaId,
                'uni_id'             => ! empty($datos['uni_id']) ? (int) $datos['uni_id'] : null,
                'ana_id'             => ! empty($datos['ana_id'] ?? $datos['ano_id'] ?? null) ? (int) ($datos['ana_id'] ?? $datos['ano_id']) : null,
                'codigo_laa'         => $codigo,
                'nombre_laa'         => substr($nombre, 0, 100),
                'fecha_inicio_laa'   => $datos['fecha_inicio_laa'] ?? $datos['fecha_inicio_la'] ?? null,
                'fecha_fin_laa'      => $datos['fecha_fin_laa'] ?? $datos['fecha_fin_la'] ?? null,
                'condicion_laa'      => $datos['condicion_laa'] ?? 'A',
                'cerrado_laa'        => $datos['cerrado_laa'] ?? 'N',
                'habilitar_nota_laa' => $datos['habilitar_nota_laa'] ?? 'N',
                'fase_operativa_laa' => $datos['fase_operativa_laa'] ?? 'Planificado',
                'status_id'          => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
                'created_id'         => auth()->id(),
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            return DB::table('lapso_academico')->insertGetId($registro, 'id_laa');
        });

        return self::obtenerPorId($id);
    }

    public static function actualizarLapso(int|string $id, array $datos): ?object
    {
        $campos = ['updated_at' => now()];

        if (array_key_exists('tla_id', $datos) || array_key_exists('tpd_id', $datos)) {
            $campos['tla_id'] = (int) ($datos['tla_id'] ?? $datos['tpd_id']);
        }
        if (array_key_exists('uni_id', $datos)) {
            $campos['uni_id'] = $datos['uni_id'] !== null ? (int) $datos['uni_id'] : null;
        }
        if (array_key_exists('ana_id', $datos) || array_key_exists('ano_id', $datos)) {
            $val = $datos['ana_id'] ?? $datos['ano_id'];
            $campos['ana_id'] = ! empty($val) ? (int) $val : null;
        }
        if (array_key_exists('codigo_laa', $datos)) {
            $codigoLimpio = substr(trim((string) $datos['codigo_laa']), 0, 20);
            if ($codigoLimpio !== '') {
                $campos['codigo_laa'] = $codigoLimpio;
            }
        }
        if (array_key_exists('nombre_laa', $datos) || array_key_exists('nombre_la', $datos)) {
            $campos['nombre_laa'] = substr(trim((string) ($datos['nombre_laa'] ?? $datos['nombre_la'])), 0, 100);
        }
        if (array_key_exists('fecha_inicio_laa', $datos) || array_key_exists('fecha_inicio_la', $datos)) {
            $campos['fecha_inicio_laa'] = $datos['fecha_inicio_laa'] ?? $datos['fecha_inicio_la'];
        }
        if (array_key_exists('fecha_fin_laa', $datos) || array_key_exists('fecha_fin_la', $datos)) {
            $campos['fecha_fin_laa'] = $datos['fecha_fin_laa'] ?? $datos['fecha_fin_la'];
        }
        if (array_key_exists('condicion_laa', $datos)) {
            $campos['condicion_laa'] = $datos['condicion_laa'];
        }
        if (array_key_exists('cerrado_laa', $datos)) {
            $campos['cerrado_laa'] = $datos['cerrado_laa'];
        }
        if (array_key_exists('habilitar_nota_laa', $datos)) {
            $campos['habilitar_nota_laa'] = $datos['habilitar_nota_laa'];
        }
        if (array_key_exists('fase_operativa_laa', $datos)) {
            $campos['fase_operativa_laa'] = $datos['fase_operativa_laa'];
        }
        if (array_key_exists('status_id', $datos)) {
            $campos['status_id'] = (int) $datos['status_id'];
        } elseif (array_key_exists('estado_la', $datos) || array_key_exists('status', $datos)) {
            $campos['status_id'] = filter_var($datos['estado_la'] ?? $datos['status'], FILTER_VALIDATE_BOOLEAN)
                ? self::STATUS_ACTIVO
                : self::STATUS_INACTIVO;
        }
        if (auth()->check()) {
            $campos['updated_id'] = auth()->id();
        }

        DB::transaction(function () use ($id, $campos) {
            if (isset($campos['codigo_laa'])) {
                self::liberarCodigoSiEliminado($campos['codigo_laa'], (int) $id);
            }

            DB::table('lapso_academico')
                ->where('id_laa', (int) $id)
                ->whereNull('deleted_at')
                ->update($campos);
        });

        return self::obtenerPorId($id);
    }

    public static function obtenerPorId(int|string $id): ?object
    {
        return DB::table('lapso_academico')
            ->leftJoin('ano_academico', 'lapso_academico.ana_id', '=', 'ano_academico.id_ana')
            ->leftJoin('tipo_lapso', 'lapso_academico.tla_id', '=', 'tipo_lapso.id_tla')
            ->select([
                'lapso_academico.*',
                'ano_academico.nombre_ana',
                'ano_academico.fecha_inicio_ana',
                'ano_academico.fecha_fin_ana',
                'ano_academico.status_id as ano_status_id',
                'tipo_lapso.nombre_tla',
                'tipo_lapso.codigo_tla',
            ])
            ->where('lapso_academico.id_laa', (int) $id)
            ->whereNull('lapso_academico.deleted_at')
            ->first();
    }

    public static function alternarEstatus(int|string $id, mixed $nuevoEstatus = null): bool
    {
        $lapso = DB::table('lapso_academico')->where('id_laa', (int) $id)->whereNull('deleted_at')->first();
        if (! $lapso) {
            throw new \DomainException('El lapso académico solicitado no existe.');
        }

        if (is_null($nuevoEstatus)) {
            $activar = ! static::esStatusActivo($lapso);
        } elseif ($nuevoEstatus === self::STATUS_ACTIVO || $nuevoEstatus === true || $nuevoEstatus === '1') {
            $activar = true;
        } else {
            $activar = false;
        }

        $statusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;

        if ($activar) {
            if (! empty($lapso->ana_id)) {
                $ano = DB::table('ano_academico')->where('id_ana', $lapso->ana_id)->whereNull('deleted_at')->first();
                if (! $ano || (int) $ano->status_id !== self::STATUS_ACTIVO) {
                    throw new \DomainException('No se puede activar el lapso porque el Año Académico al que pertenece se encuentra inactivo o cerrado.');
                }
            }

            $otroActivo = DB::table('lapso_academico')
                ->where('id_laa', '!=', (int) $id)
                ->whereNull('deleted_at')
                ->where('status_id', self::STATUS_ACTIVO)
                ->exists();

            if ($otroActivo) {
                throw new \DomainException('Ya existe otro Lapso Académico activo en el sistema. Solo puede haber un lapso activo a la vez.');
            }
        }

        return DB::transaction(function () use ($id, $statusId, $activar) {
            $now = now();
            $userId = auth()->id();

            DB::table('lapso_academico')
                ->where('id_laa', (int) $id)
                ->update([
                    'status_id'  => $statusId,
                    'updated_id' => $userId,
                    'updated_at' => $now,
                ]);

            if (! $activar) {
                $cohorteIds = DB::table('cohorte')
                    ->where('laa_id', (int) $id)
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

            return $activar;
        });
    }
}
