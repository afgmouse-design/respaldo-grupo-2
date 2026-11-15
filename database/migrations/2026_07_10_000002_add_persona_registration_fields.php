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
            if (!Schema::hasColumn('persona', 'per_tipo_documento')) {
                $table->string('per_tipo_documento', 1)->nullable()->after('per_documento');
            }
            if (!Schema::hasColumn('persona', 'per_primer_nombre')) {
                $table->string('per_primer_nombre', 100)->nullable()->after('per_apellidos');
            }
            if (!Schema::hasColumn('persona', 'per_segundo_nombre')) {
                $table->string('per_segundo_nombre', 100)->nullable()->after('per_primer_nombre');
            }
            if (!Schema::hasColumn('persona', 'per_primer_apellido')) {
                $table->string('per_primer_apellido', 100)->nullable()->after('per_segundo_nombre');
            }
            if (!Schema::hasColumn('persona', 'per_segundo_apellido')) {
                $table->string('per_segundo_apellido', 100)->nullable()->after('per_primer_apellido');
            }
            if (!Schema::hasColumn('persona', 'per_email_local')) {
                $table->string('per_email_local', 100)->nullable()->after('per_email');
            }
            if (!Schema::hasColumn('persona', 'per_email_domain')) {
                $table->string('per_email_domain', 100)->nullable()->after('per_email_local');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_movil_codigo')) {
                $table->string('per_telefono_movil_codigo', 4)->nullable()->after('per_telefono_movil');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_movil_numero')) {
                $table->string('per_telefono_movil_numero', 15)->nullable()->after('per_telefono_movil_codigo');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_fijo_codigo')) {
                $table->string('per_telefono_fijo_codigo', 4)->nullable()->after('per_telefono_fijo');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_fijo_numero')) {
                $table->string('per_telefono_fijo_numero', 15)->nullable()->after('per_telefono_fijo_codigo');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_trabajo_codigo')) {
                $table->string('per_telefono_trabajo_codigo', 4)->nullable()->after('per_telefono_trabajo');
            }
            if (!Schema::hasColumn('persona', 'per_telefono_trabajo_numero')) {
                $table->string('per_telefono_trabajo_numero', 15)->nullable()->after('per_telefono_trabajo_codigo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            if (Schema::hasColumn('persona', 'per_tipo_documento')) {
                $table->dropColumn('per_tipo_documento');
            }
            if (Schema::hasColumn('persona', 'per_primer_nombre')) {
                $table->dropColumn('per_primer_nombre');
            }
            if (Schema::hasColumn('persona', 'per_segundo_nombre')) {
                $table->dropColumn('per_segundo_nombre');
            }
            if (Schema::hasColumn('persona', 'per_primer_apellido')) {
                $table->dropColumn('per_primer_apellido');
            }
            if (Schema::hasColumn('persona', 'per_segundo_apellido')) {
                $table->dropColumn('per_segundo_apellido');
            }
            if (Schema::hasColumn('persona', 'per_email_local')) {
                $table->dropColumn('per_email_local');
            }
            if (Schema::hasColumn('persona', 'per_email_domain')) {
                $table->dropColumn('per_email_domain');
            }
            if (Schema::hasColumn('persona', 'per_telefono_movil_codigo')) {
                $table->dropColumn('per_telefono_movil_codigo');
            }
            if (Schema::hasColumn('persona', 'per_telefono_movil_numero')) {
                $table->dropColumn('per_telefono_movil_numero');
            }
            if (Schema::hasColumn('persona', 'per_telefono_fijo_codigo')) {
                $table->dropColumn('per_telefono_fijo_codigo');
            }
            if (Schema::hasColumn('persona', 'per_telefono_fijo_numero')) {
                $table->dropColumn('per_telefono_fijo_numero');
            }
            if (Schema::hasColumn('persona', 'per_telefono_trabajo_codigo')) {
                $table->dropColumn('per_telefono_trabajo_codigo');
            }
            if (Schema::hasColumn('persona', 'per_telefono_trabajo_numero')) {
                $table->dropColumn('per_telefono_trabajo_numero');
            }
        });
    }
};
