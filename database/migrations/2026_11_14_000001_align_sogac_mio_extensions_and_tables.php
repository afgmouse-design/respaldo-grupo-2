<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración consolidada e idempotente de alineación con el esquema maestro `sogac_mio.sql`.
 *
 * Propósito:
 *  - Crear exclusivamente las tablas nuevas requeridas por los módulos de Gestión Curricular,
 *    Geografía, Años Académicos y Programación que no existen en `sogac_mio.sql`:
 *    `pais`, `ano_academico`, `tipo_programa`, `modalidad_estudio`, `regimen_estudio`.
 *  - Crear o adaptar las tablas exclusivas del repositorio (`escala_nota`, `corte_lapso`,
 *    `evento_calendario`, `tipo_seccion`) a las convenciones oficiales del dump:
 *    `status_id integer NOT NULL DEFAULT 1` (sin booleano `status`), auditoría `bigint`
 *    y claves foráneas hacia las PK reales del dump (`id_esm`, `id_laa`).
 *  - Aplicar únicamente extensiones aditivas (`ADD COLUMN NULL/DEFAULT`, FKs faltantes e índice
 *    compuesto anti-choque) sobre tablas compartidas sin usar DROP, RENAME ni ALTER de tipo.
 *
 * Historial:
 *  - 2026-11-14: Creación inicial consolidando la Fase B de alineación sobre `sogac_mio.sql`.
 */
return new class extends Migration
{
    /**
     * Ejecuta la alineación aditiva del esquema en base de datos.
     */
    public function up(): void
    {
        $this->createNewMasterTables();
        $this->createOrAdaptOwnedTables();
        $this->applyApprovedAdditiveAlterations();
    }

    /**
     * Revierte exclusivamente las columnas, restricciones, índices y tablas creadas por esta migración.
     * Nunca elimina ni modifica columnas o tablas preexistentes en `sogac_mio.sql`.
     */
    public function down(): void
    {
        $this->revertAdditiveAlterations();

        foreach ([
            'tipo_seccion',
            'evento_calendario',
            'corte_lapso',
            'escala_nota',
            'regimen_estudio',
            'modalidad_estudio',
            'tipo_programa',
            'ano_academico',
            'pais',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * Crea las tablas maestras faltantes en `sogac_mio.sql` siguiendo estrictamente sus convenciones.
     */
    private function createNewMasterTables(): void
    {
        if (! Schema::hasTable('pais')) {
            Schema::create('pais', function (Blueprint $table) {
                $table->bigIncrements('id_pai');
                $table->string('nombre_pai', 255)->unique('uq_pais_nombre_pai');
                $this->defineStatusAndAuditColumns($table);
            });
            $this->ensureStatusAndAuditForeignKeys('pais');
        }

        if (! Schema::hasTable('ano_academico')) {
            Schema::create('ano_academico', function (Blueprint $table) {
                $table->increments('id_ana');
                $table->string('nombre_ana', 100);
                $table->date('fecha_inicio_ana')->nullable();
                $table->date('fecha_fin_ana')->nullable();
                $this->defineStatusAndAuditColumns($table);
            });
            $this->ensureStatusAndAuditForeignKeys('ano_academico');
        }

        if (! Schema::hasTable('tipo_programa')) {
            Schema::create('tipo_programa', function (Blueprint $table) {
                $table->increments('id_tpg');
                $table->string('codigo_tpg', 20)->unique('uq_tipo_programa_codigo_tpg');
                $table->string('nombre_tpg', 100);
                $table->text('descripcion_tpg')->nullable();
                $table->boolean('conducente_grado_tpg')->default(true);
                $this->defineStatusAndAuditColumns($table);
            });
            $this->ensureStatusAndAuditForeignKeys('tipo_programa');
        }

        $this->createSimpleCatalogTable('modalidad_estudio', 'id_moe', 'moe');
        $this->createSimpleCatalogTable('regimen_estudio', 'id_ree', 'ree');
    }

    /**
     * Crea o adapta a convenciones del dump las tablas pertenecientes solo al repositorio (PASO 3).
     */
    private function createOrAdaptOwnedTables(): void
    {
        if (! Schema::hasTable('escala_nota')) {
            Schema::create('escala_nota', function (Blueprint $table) {
                $table->increments('id_esn');
                $table->decimal('nota_original_esn', 8, 2);
                $table->decimal('nota_100_esn', 8, 2)->nullable();
                $table->decimal('nota_20_esn', 8, 2)->nullable();
                $table->integer('esm_id');
                $this->defineStatusAndAuditColumns($table);
            });
        }
        $this->adaptOwnedTableConvention('escala_nota');
        $this->addForeignKeyIfMissing('escala_nota', 'fk_escala_nota_esm_id', 'esm_id', 'escala_medicion', 'id_esm');

        if (! Schema::hasTable('corte_lapso')) {
            Schema::create('corte_lapso', function (Blueprint $table) {
                $table->increments('id_col');
                $table->integer('numero_corte_col')->nullable();
                $table->decimal('porcentaje_valor_col', 8, 2)->nullable();
                $table->timestamp('fecha_apertura_notas_col', 0)->nullable();
                $table->timestamp('fecha_cierre_notas_col', 0)->nullable();
                $table->integer('laa_id');
                $this->defineStatusAndAuditColumns($table);
            });
        }
        $this->adaptOwnedTableConvention('corte_lapso');
        $this->addForeignKeyIfMissing('corte_lapso', 'fk_corte_lapso_laa_id', 'laa_id', 'lapso_academico', 'id_laa');

        if (! Schema::hasTable('evento_calendario')) {
            Schema::create('evento_calendario', function (Blueprint $table) {
                $table->increments('id_evc');
                $table->string('tipo_evento_evc', 255)->nullable();
                $table->date('fecha_inicio_evc')->nullable();
                $table->date('fecha_fin_evc')->nullable();
                $table->integer('laa_id');
                $this->defineStatusAndAuditColumns($table);
            });
        }
        $this->adaptOwnedTableConvention('evento_calendario');
        $this->addForeignKeyIfMissing('evento_calendario', 'fk_evento_calendario_laa_id', 'laa_id', 'lapso_academico', 'id_laa');

        $this->createSimpleCatalogTable('tipo_seccion', 'id_tis', 'tis');
        $this->adaptOwnedTableConvention('tipo_seccion');
    }

    /**
     * Aplica las extensiones aditivas aprobadas (ADD COLUMN nullable/default, FKs e índice).
     */
    private function applyApprovedAdditiveAlterations(): void
    {
        $this->addForeignKeyIfMissing('estado', 'fk_estado_pai_id', 'pai_id', 'pais', 'id_pai', 'SET NULL');

        if (Schema::hasTable('tipo_lapso')) {
            Schema::table('tipo_lapso', function (Blueprint $table) {
                if (! Schema::hasColumn('tipo_lapso', 'nomenclatura_tla')) {
                    $table->string('nomenclatura_tla', 50)->nullable();
                }
                if (! Schema::hasColumn('tipo_lapso', 'descripcion_tla')) {
                    $table->text('descripcion_tla')->nullable();
                }
            });
        }

        if (Schema::hasTable('lapso_academico')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                if (! Schema::hasColumn('lapso_academico', 'fase_operativa_laa')) {
                    $table->string('fase_operativa_laa', 50)->nullable()->default('Planificado');
                }
            });
            $this->addForeignKeyIfMissing('lapso_academico', 'fk_lapso_academico_ana_id', 'ana_id', 'ano_academico', 'id_ana', 'SET NULL');
        }

        if (Schema::hasTable('programa')) {
            Schema::table('programa', function (Blueprint $table) {
                if (! Schema::hasColumn('programa', 'codigo_prg')) {
                    $table->string('codigo_prg', 30)->nullable();
                }
                if (! Schema::hasColumn('programa', 'titulo_prg')) {
                    $table->string('titulo_prg', 255)->nullable();
                }
                if (! Schema::hasColumn('programa', 'tpg_id')) {
                    $table->integer('tpg_id')->nullable();
                }
                if (! Schema::hasColumn('programa', 'ree_id')) {
                    $table->integer('ree_id')->nullable();
                }
                if (! Schema::hasColumn('programa', 'moe_id')) {
                    $table->integer('moe_id')->nullable();
                }
            });
            $this->addForeignKeyIfMissing('programa', 'fk_programa_tpg_id', 'tpg_id', 'tipo_programa', 'id_tpg', 'SET NULL');
            $this->addForeignKeyIfMissing('programa', 'fk_programa_ree_id', 'ree_id', 'regimen_estudio', 'id_ree', 'SET NULL');
            $this->addForeignKeyIfMissing('programa', 'fk_programa_moe_id', 'moe_id', 'modalidad_estudio', 'id_moe', 'SET NULL');
        }

        if (Schema::hasTable('turno')) {
            Schema::table('turno', function (Blueprint $table) {
                if (! Schema::hasColumn('turno', 'hora_inicio_tur')) {
                    $table->time('hora_inicio_tur', 0)->nullable();
                }
                if (! Schema::hasColumn('turno', 'hora_fin_tur')) {
                    $table->time('hora_fin_tur', 0)->nullable();
                }
                if (! Schema::hasColumn('turno', 'descripcion_tur')) {
                    $table->text('descripcion_tur')->nullable();
                }
            });
        }

        if (Schema::hasTable('malla_curricular')) {
            Schema::table('malla_curricular', function (Blueprint $table) {
                if (! Schema::hasColumn('malla_curricular', 'version_pensum_mac')) {
                    $table->string('version_pensum_mac', 50)->nullable();
                }
                if (! Schema::hasColumn('malla_curricular', 'observacion_mac')) {
                    $table->text('observacion_mac')->nullable();
                }
            });
        }

        if (Schema::hasTable('malla_curricular_detalle')) {
            Schema::table('malla_curricular_detalle', function (Blueprint $table) {
                if (! Schema::hasColumn('malla_curricular_detalle', 'nota_minima_aprobatoria_mcd')) {
                    $table->decimal('nota_minima_aprobatoria_mcd', 4, 2)->nullable();
                }
                if (! Schema::hasColumn('malla_curricular_detalle', 'semestre_mcd')) {
                    $table->integer('semestre_mcd')->nullable()->default(1);
                }
                if (! Schema::hasColumn('malla_curricular_detalle', 'etapa_curricular_mcd')) {
                    $table->string('etapa_curricular_mcd', 50)->nullable()->default('TSU');
                }
            });
        }

        if (Schema::hasTable('unidad_curricular')) {
            Schema::table('unidad_curricular', function (Blueprint $table) {
                if (! Schema::hasColumn('unidad_curricular', 'codigo_anterior_unc')) {
                    $table->string('codigo_anterior_unc', 30)->nullable();
                }
            });
        }

        if (Schema::hasTable('seccion')) {
            Schema::table('seccion', function (Blueprint $table) {
                if (! Schema::hasColumn('seccion', 'tis_id')) {
                    $table->integer('tis_id')->nullable();
                }
                if (! Schema::hasColumn('seccion', 'tur_id')) {
                    $table->integer('tur_id')->nullable();
                }
                if (! Schema::hasColumn('seccion', 'cos_id')) {
                    $table->integer('cos_id')->nullable();
                }
                if (! Schema::hasColumn('seccion', 'mac_id')) {
                    $table->integer('mac_id')->nullable();
                }
                if (! Schema::hasColumn('seccion', 'codigo_sec')) {
                    $table->string('codigo_sec', 50)->nullable();
                }
                if (! Schema::hasColumn('seccion', 'semestre_sec')) {
                    $table->integer('semestre_sec')->nullable()->default(1);
                }
                if (! Schema::hasColumn('seccion', 'trayecto_sec')) {
                    $table->integer('trayecto_sec')->nullable()->default(1);
                }
            });
            $this->addForeignKeyIfMissing('seccion', 'fk_seccion_tis_id', 'tis_id', 'tipo_seccion', 'id_tis', 'SET NULL');
            $this->addForeignKeyIfMissing('seccion', 'fk_seccion_tur_id', 'tur_id', 'turno', 'id_tur', 'SET NULL');
            $this->addForeignKeyIfMissing('seccion', 'fk_seccion_cos_id', 'cos_id', 'cohorte_sede', 'id_cos', 'SET NULL');
            $this->addForeignKeyIfMissing('seccion', 'fk_seccion_mac_id', 'mac_id', 'malla_curricular', 'id_mac', 'SET NULL');
        }

        if (Schema::hasTable('horario')) {
            Schema::table('horario', function (Blueprint $table) {
                if (! Schema::hasColumn('horario', 'tipo_sesion_hor')) {
                    $table->string('tipo_sesion_hor', 50)->nullable()->default('Teorica');
                }
            });
            $this->ensureIndexIfMissing(
                'horario',
                'idx_horario_anti_choque',
                ['aul_id', 'dia_semana_hor', 'hora_inicio_hor', 'hora_fin_hor']
            );
        }

        if (Schema::hasTable('carga_academica')) {
            Schema::table('carga_academica', function (Blueprint $table) {
                if (! Schema::hasColumn('carga_academica', 'tipo_contratacion_caa')) {
                    $table->string('tipo_contratacion_caa', 50)->nullable()->default('Titular');
                }
                if (! Schema::hasColumn('carga_academica', 'horas_semanales_caa')) {
                    $table->integer('horas_semanales_caa')->nullable()->default(4);
                }
            });
        }
    }

    /**
     * Revierte únicamente las columnas, FKs e índice agregados por `applyApprovedAdditiveAlterations()`.
     */
    private function revertAdditiveAlterations(): void
    {
        $this->dropColumnsIfExist('carga_academica', ['tipo_contratacion_caa', 'horas_semanales_caa']);

        $this->dropIndexIfExists('horario', 'idx_horario_anti_choque');
        $this->dropColumnsIfExist('horario', ['tipo_sesion_hor']);

        foreach (['fk_seccion_tis_id', 'fk_seccion_tur_id', 'fk_seccion_cos_id', 'fk_seccion_mac_id'] as $fk) {
            $this->dropForeignKeyIfExists('seccion', $fk);
        }
        $this->dropColumnsIfExist('seccion', ['tis_id', 'tur_id', 'cos_id', 'mac_id', 'codigo_sec', 'semestre_sec', 'trayecto_sec']);

        $this->dropColumnsIfExist('unidad_curricular', ['codigo_anterior_unc']);
        $this->dropColumnsIfExist('malla_curricular_detalle', ['nota_minima_aprobatoria_mcd', 'semestre_mcd', 'etapa_curricular_mcd']);
        $this->dropColumnsIfExist('malla_curricular', ['version_pensum_mac', 'observacion_mac']);
        $this->dropColumnsIfExist('turno', ['hora_inicio_tur', 'hora_fin_tur', 'descripcion_tur']);

        foreach (['fk_programa_tpg_id', 'fk_programa_ree_id', 'fk_programa_moe_id'] as $fk) {
            $this->dropForeignKeyIfExists('programa', $fk);
        }
        $this->dropColumnsIfExist('programa', ['codigo_prg', 'titulo_prg', 'tpg_id', 'ree_id', 'moe_id']);

        $this->dropForeignKeyIfExists('lapso_academico', 'fk_lapso_academico_ana_id');
        $this->dropColumnsIfExist('lapso_academico', ['fase_operativa_laa']);

        $this->dropColumnsIfExist('tipo_lapso', ['nomenclatura_tla', 'descripcion_tla']);
        $this->dropForeignKeyIfExists('estado', 'fk_estado_pai_id');
    }

    /**
     * Crea una tabla de catálogo estándar (codigo_<suf>, nombre_<suf>, descripcion_<suf>) si no existe.
     */
    private function createSimpleCatalogTable(string $tableName, string $pkColumn, string $suffix): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($tableName, $pkColumn, $suffix) {
            $table->increments($pkColumn);
            $table->string("codigo_{$suffix}", 20)->unique("uq_{$tableName}_codigo_{$suffix}");
            $table->string("nombre_{$suffix}", 100);
            $table->text("descripcion_{$suffix}")->nullable();
            $this->defineStatusAndAuditColumns($table);
        });

        $this->ensureStatusAndAuditForeignKeys($tableName);
    }

    /**
     * Define las columnas estándar de estado lógico y auditoría de `sogac_mio.sql`.
     */
    private function defineStatusAndAuditColumns(Blueprint $table): void
    {
        $table->integer('status_id')->default(1);
        $table->timestamps(0);
        $table->softDeletes('deleted_at', 0);
        $table->unsignedBigInteger('created_id')->nullable();
        $table->unsignedBigInteger('updated_id')->nullable();
    }

    /**
     * Asegura que una tabla exclusiva del repositorio cumpla `status_id` + auditoría `bigint` (sin `status` bool).
     */
    private function adaptOwnedTableConvention(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'status_id')) {
                $table->integer('status_id')->default(1);
            }
            if (! Schema::hasColumn($tableName, 'created_at')) {
                $table->timestamp('created_at', 0)->nullable();
            }
            if (! Schema::hasColumn($tableName, 'updated_at')) {
                $table->timestamp('updated_at', 0)->nullable();
            }
            if (! Schema::hasColumn($tableName, 'deleted_at')) {
                $table->softDeletes('deleted_at', 0);
            }
            if (! Schema::hasColumn($tableName, 'created_id')) {
                $table->unsignedBigInteger('created_id')->nullable();
            }
            if (! Schema::hasColumn($tableName, 'updated_id')) {
                $table->unsignedBigInteger('updated_id')->nullable();
            }
            if (Schema::hasColumn($tableName, 'status')) {
                $table->dropColumn('status');
            }
        });

        $this->ensureStatusAndAuditForeignKeys($tableName);
    }

    /**
     * Registra las claves foráneas hacia `status(id_sta)` y `users(id)` si no existen.
     */
    private function ensureStatusAndAuditForeignKeys(string $tableName): void
    {
        $this->addForeignKeyIfMissing($tableName, "fk_{$tableName}_status_id", 'status_id', 'status', 'id_sta');
        $this->addForeignKeyIfMissing($tableName, "fk_{$tableName}_created_id", 'created_id', 'users', 'id', 'SET NULL');
        $this->addForeignKeyIfMissing($tableName, "fk_{$tableName}_updated_id", 'updated_id', 'users', 'id', 'SET NULL');
    }

    /**
     * Agrega una clave foránea por nombre explícito verificando existencia en tablas y catálogos PG.
     */
    private function addForeignKeyIfMissing(
        string $tableName,
        string $constraintName,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete = 'RESTRICT'
    ): void {
        if (! Schema::hasTable($tableName) || ! Schema::hasTable($referencedTable) || ! Schema::hasColumn($tableName, $column)) {
            return;
        }

        if ($this->constraintExists($tableName, $constraintName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($constraintName, $column, $referencedTable, $referencedColumn, $onDelete) {
            $fk = $table->foreign($column, $constraintName)
                ->references($referencedColumn)
                ->on($referencedTable);

            match (strtoupper($onDelete)) {
                'SET NULL' => $fk->nullOnDelete(),
                'CASCADE' => $fk->cascadeOnDelete(),
                default => $fk->restrictOnDelete(),
            };
        });
    }

    /**
     * Elimina una clave foránea únicamente si existe en PostgreSQL/BD actual.
     */
    private function dropForeignKeyIfExists(string $tableName, string $constraintName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->constraintExists($tableName, $constraintName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($constraintName) {
            $table->dropForeign($constraintName);
        });
    }

    /**
     * Crea un índice si no existe en la tabla objetivo.
     */
    private function ensureIndexIfMissing(string $tableName, string $indexName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columns as $col) {
            if (! Schema::hasColumn($tableName, $col)) {
                return;
            }
        }

        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    /**
     * Elimina un índice por nombre únicamente si existe.
     */
    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    /**
     * Elimina únicamente las columnas listadas que existan en la tabla.
     */
    private function dropColumnsIfExist(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = array_values(array_filter($columns, fn (string $col) => Schema::hasColumn($tableName, $col)));
        if ($existing === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }

    /**
     * Verifica existencia de una restricción en el esquema activo.
     */
    private function constraintExists(string $tableName, string $constraintName): bool
    {
        if (DB::getDriverName() === 'pgsql') {
            return DB::table('pg_constraint as c')
                ->join('pg_class as t', 't.oid', '=', 'c.conrelid')
                ->join('pg_namespace as n', 'n.oid', '=', 't.relnamespace')
                ->where('n.nspname', 'public')
                ->where('t.relname', $tableName)
                ->where('c.conname', $constraintName)
                ->exists();
        }

        return false;
    }

    /**
     * Verifica existencia de un índice en el esquema activo.
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        if (DB::getDriverName() === 'pgsql') {
            return DB::table('pg_indexes')
                ->where('schemaname', 'public')
                ->where('tablename', $tableName)
                ->where('indexname', $indexName)
                ->exists();
        }

        return false;
    }
};
