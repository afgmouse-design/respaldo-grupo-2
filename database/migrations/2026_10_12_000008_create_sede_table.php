<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `sede` (Módulo 2 - Organización Institucional y Sedes).
 *
 * Propósito: Almacena los núcleos, extensiones físicas y sedes rectorales
 * pertenecientes a la institución universitaria matriz.
 *
 * Convenciones y Requisitos Oficiales:
 *  - Clave primaria: `id_sed`.
 *  - Relaciones foráneas hacia `universidad` (`id_uni`), `localidad` (`id_loc`) y `condicion` (`id_cdn`).
 *  - SoftDeletes (`deleted_at`), auditoría con `created_id`/`updated_id` y estatus lógico.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('sede', function (Blueprint $table) {
            $table->id('id_sed')->comment('Identificador autoincremental de la sede universitaria');
            $table->string('nombre_sed', 255)->comment('Denominación oficial de la sede o núcleo');
            $table->string('siglas_sed', 20)->nullable()->comment('Siglas o código corto de identificación (ej. NUC-ACA)');
            $table->string('codigo_ministerio_sed', 30)->nullable()->comment('Código específico asignado por entes rectores (MPPEU/OPSU)');
            $table->text('direccion_sed')->nullable()->comment('Dirección física detallada de la sede');
            $table->string('telefono_sed', 50)->nullable()->comment('Número telefónico de contacto');
            $table->string('correo_sed', 255)->nullable()->comment('Correo electrónico oficial de la sede');
            $table->string('gaceta_sed', 255)->nullable()->comment('Número de Gaceta Oficial de creación de la sede');
            $table->date('fecha_gaceta_sed')->nullable()->comment('Fecha de publicación oficial de la Gaceta');
            $table->string('resolucion_sed', 255)->nullable()->comment('Número de resolución del Consejo Universitario o CNU');
            $table->date('fecha_resolucion_sed')->nullable()->comment('Fecha de aprobación de la resolución');
            $table->date('fecha_inicio_sed')->nullable()->comment('Fecha oficial de inicio de actividades');
            $table->date('fecha_fin_sed')->nullable()->comment('Fecha de cierre o cese de operaciones (nullable si activa)');

            // Claves Foráneas
            $table->foreignId('uni_id')
                ->constrained('universidad', 'id_uni')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Universidad matriz a la que pertenece esta sede');

            $table->foreignId('loc_id')
                ->constrained('localidad', 'id_loc')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Localidad geográfica donde está ubicada la sede');

            $table->foreignId('cdn_id')
                ->nullable()
                ->constrained('condicion', 'id_cdn')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->comment('Condición o ámbito funcional (ej. Sede Principal, Extensión)');

            // Estatus y Borrado Lógico
            $table->boolean('status')->default(true)->comment('Estatus lógico reactivo (true=Activo, false=Inactivo)');
            $table->integer('status_id')->default(1)->comment('Estado numérico de compatibilidad ER');

            // Auditoría
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario creador');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario actualizador');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['uni_id', 'nombre_sed']);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('sede');
    }
};

