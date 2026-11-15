<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `universidad` (Módulo 2 - Organización Institucional y Sedes).
 *
 * Propósito: Almacena la entidad institucional matriz de la universidad
 * registrada en Gaceta Oficial, rectorado y datos de contacto principales.
 *
 * Convenciones (AGENTS.md §3):
 *  - Nombre de tabla en singular explícito (`universidad`).
 *  - Clave primaria personalizada (`id_uni`).
 *  - Borrado lógico booleano (`status`, true = Activo, false = Inactivo).
 *  - Auditoría de usuario con `created_id` y `updated_id`.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('universidad', function (Blueprint $table) {
            $table->id('id_uni')->comment('Identificador autoincremental único de la institución matriz');
            $table->string('codigo_ministerio_uni', 30)->nullable()->comment('Código oficial asignado por OPSU/MPPEU');
            $table->string('nombre_uni', 255)->comment('Nombre legal completo de la universidad');
            $table->string('siglas_uni', 20)->nullable()->comment('Abreviatura oficial de la institución (ej. UPTP)');
            $table->string('rif_uni', 20)->nullable()->comment('Registro de Información Fiscal institucional ante el SENIAT');
            $table->string('gaceta_uni', 255)->nullable()->comment('Número y datos de la Gaceta Oficial de creación');
            $table->date('fecha_gaceta_uni')->nullable()->comment('Fecha de publicación de la Gaceta Oficial');
            $table->string('decreto_uni', 255)->nullable()->comment('Número de decreto o resolución de creación');
            $table->date('fecha_decreto_uni')->nullable()->comment('Fecha de promulgación del decreto');
            $table->string('telefono_uni', 50)->nullable()->comment('Número telefónico oficial del rectorado');
            $table->string('correo_uni', 255)->nullable()->comment('Correo electrónico institucional principal');
            $table->string('sitio_web_uni', 255)->nullable()->comment('Dirección URL del portal web oficial');
            $table->text('logo_uni')->nullable()->comment('Ruta o identificador del archivo del isotipo/escudo');
            $table->date('fecha_inicio_uni')->nullable()->comment('Fecha oficial de inicio de actividades institucionales');
            $table->date('fecha_fin_uni')->nullable()->comment('Fecha de cese de operaciones (nullable si activa)');

            // Relaciones foráneas
            $table->foreignId('loc_id')
                ->nullable()
                ->constrained('localidad', 'id_loc')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->comment('Localidad geográfica donde se ubica el rectorado');

            $table->unsignedBigInteger('dpi_id')->nullable()->comment('Tipo de dependencia institucional (Ref: dependencia_institucion)');
            $table->unsignedBigInteger('cdn_id')->nullable()->comment('Condición institucional (Ref: condicion)');

            // Borrado lógico (AGENTS.md §3)
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');

            // Auditoría
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario que creó el registro');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario que actualizó el registro');

            $table->timestampsTz();

            $table->unique('nombre_uni');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('universidad');
    }
};

