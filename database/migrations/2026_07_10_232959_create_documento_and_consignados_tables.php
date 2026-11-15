<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documento', function (Blueprint $table) {
            $table->increments('doc_codigo');
            $table->string('doc_nombre', 200)->unique();
            $table->char('doc_tipo', 1)->nullable();
            $table->char('doc_estatus', 1)->default('A');
        });

        Schema::create('documentos_consignados', function (Blueprint $table) {
            $table->increments('dco_codigo');
            $table->unsignedInteger('dco_cod_documento');
            $table->string('dco_cedula', 12);
            $table->char('dco_estatus', 1)->default('A');
            $table->date('dco_fecha_consignacion')->useCurrent();
            $table->text('dco_observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('dco_cod_documento')->references('doc_codigo')->on('documento')->onDelete('cascade');
            $table->foreign('dco_cedula')->references('per_cedula')->on('persona')->onDelete('cascade');
        });

        // Insert initial documents
        $documents = [
            'Boucher Del Depósito Bancario',
            'Certificado De Participación Opsu',
            'Copia De La Cédula De Identidad',
            'Copia De La Partida De Nacimiento',
            'Copia Del Título De Bachiller',
            'Fotocopia Ampliada De La Cédula De Identidad',
            'Fotocopia Con Vista Al Original De La Partida De Nacimiento',
            'Fotocopia Con Vista Al Original De Las Notas Certificadas De 1Ro. A 5To. Año Con Sus Respectivos Timbres Fiscales',
            'Fotocopia Con Vista Al Original Del Título De Bachiller',
            'Notas Certificadas De Tsu Con Sus Timbres Fiscales (Egresados De Otras Casas De Estudio)',
            'Planilla De Egreso',
            'Planilla Registro Opsu',
            'Solvencia Institucional'
        ];

        foreach ($documents as $doc) {
            DB::table('documento')->insert([
                'doc_nombre' => $doc,
                'doc_tipo' => 'O',
                'doc_estatus' => 'A'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_consignados');
        Schema::dropIfExists('documento');
    }
};
