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
        $documents = [
            'Fondo Negro del Título de Pregrado Registrado',
            'Fondo Negro del Título de Postgrado Registrado (si aplica)',
            'Síntesis Curricular con Soportes',
            'Copia de la Cédula de Identidad Ampliada',
            'Copia de la Constancia del RIF Vigente',
            'Certificado de Aptitud Mental y Física',
            'Constancia de Trabajo Docente Previa',
            'Acreditación de Saberes (si aplica)'
        ];

        foreach ($documents as $doc) {
            \Illuminate\Support\Facades\DB::table('documento')->insert([
                'doc_nombre' => $doc,
                'doc_tipo' => 'D',
                'doc_estatus' => 'A'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('documento')
            ->where('doc_tipo', 'D')
            ->delete();
    }
};
