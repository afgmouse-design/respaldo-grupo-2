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
            'Copia de la Cédula de Identidad (Autoridad)',
            'Curriculum Vitae (Autoridad)',
            'Título Universitario (Autoridad)'
        ];

        foreach ($documents as $doc) {
            \Illuminate\Support\Facades\DB::table('documento')->insert([
                'doc_nombre' => $doc,
                'doc_tipo' => 'U',
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
            ->where('doc_tipo', 'U')
            ->delete();
    }
};
