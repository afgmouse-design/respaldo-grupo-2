<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            // Expand existing column lengths
            $table->string('per_estatus', 20)->default('ACTIVO')->change();
            $table->string('doc_cod_profesion', 255)->nullable()->change();
            $table->string('doc_cod_categoria_actual', 255)->nullable()->change();
            $table->string('doc_cod_dedicacion', 255)->nullable()->change();
            $table->string('doc_cod_condicion_laboral', 255)->nullable()->change();
            $table->string('doc_cod_categoria_inicial', 255)->nullable()->change();

            // Add new general Persona attributes from diagram & forms
            if (!Schema::hasColumn('persona', 'per_sexo')) {
                $table->string('per_sexo', 1)->nullable()->after('per_genero');
            }
            if (!Schema::hasColumn('persona', 'per_email_personal')) {
                $table->string('per_email_personal', 150)->nullable()->after('per_email');
            }
            if (!Schema::hasColumn('persona', 'per_email_institucional')) {
                $table->string('per_email_institucional', 150)->nullable()->after('per_email_personal');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_principal')) {
                $table->string('per_telefono_principal', 20)->nullable()->after('per_telefono_movil');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_emergencia')) {
                $table->string('per_telefono_emergencia', 20)->nullable()->after('per_telefono_trabajo_numero');
            }
            if (!Schema::hasColumn('persona', 'per_direccion_habitacion')) {
                $table->text('per_direccion_habitacion')->nullable()->after('per_telefono_emergencia');
            }
            if (!Schema::hasColumn('persona', 'per_foto_url')) {
                $table->string('per_foto_url', 500)->nullable();
            }

            // Student specific fields
            if (!Schema::hasColumn('persona', 'est_sni')) {
                $table->string('est_sni', 30)->nullable();
            }
            if (!Schema::hasColumn('persona', 'est_solvencia_adm')) {
                $table->boolean('est_solvencia_adm')->default(false);
            }
            if (!Schema::hasColumn('persona', 'est_solvencia_aca')) {
                $table->boolean('est_solvencia_aca')->default(false);
            }
            if (!Schema::hasColumn('persona', 'est_solvencia_bib')) {
                $table->boolean('est_solvencia_bib')->default(false);
            }
            if (!Schema::hasColumn('persona', 'est_privado_libertad')) {
                $table->boolean('est_privado_libertad')->default(false);
            }
            if (!Schema::hasColumn('persona', 'est_ira')) {
                $table->decimal('est_ira', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('persona', 'est_creditos_aprobados')) {
                $table->integer('est_creditos_aprobados')->default(0);
            }
            if (!Schema::hasColumn('persona', 'est_condicion')) {
                $table->string('est_condicion', 30)->default('REGULAR');
            }
            if (!Schema::hasColumn('persona', 'est_culminacion_aca')) {
                $table->boolean('est_culminacion_aca')->default(false);
            }

            // Program adscription fields
            if (!Schema::hasColumn('persona', 'id_programa')) {
                $table->string('id_programa', 50)->nullable();
            }
            if (!Schema::hasColumn('persona', 'id_sede')) {
                $table->string('id_sede', 50)->nullable();
            }
            if (!Schema::hasColumn('persona', 'rol_en_programa')) {
                $table->string('rol_en_programa', 30)->nullable();
            }

            // Docent specific fields
            if (!Schema::hasColumn('persona', 'doc_condicion_laboral')) {
                $table->string('doc_condicion_laboral', 100)->nullable();
            }
            if (!Schema::hasColumn('persona', 'secciones_asignadas')) {
                $table->json('secciones_asignadas')->nullable();
            }

            // Timestamps
            if (!Schema::hasColumn('persona', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('persona', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn([
                'per_sexo',
                'per_email_personal',
                'per_email_institucional',
                'per_telefono_principal',
                'per_telefono_emergencia',
                'per_direccion_habitacion',
                'per_foto_url',
                'est_sni',
                'est_solvencia_adm',
                'est_solvencia_aca',
                'est_solvencia_bib',
                'est_privado_libertad',
                'est_ira',
                'est_creditos_aprobados',
                'est_condicion',
                'est_culminacion_aca',
                'id_programa',
                'id_sede',
                'rol_en_programa',
                'doc_condicion_laboral',
                'secciones_asignadas',
                'created_at',
                'updated_at',
            ]);
        });
    }
};
