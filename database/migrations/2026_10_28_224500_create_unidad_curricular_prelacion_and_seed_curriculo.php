<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tabla unidad_curricular_prelacion si no existe
        if (!Schema::hasTable('unidad_curricular_prelacion')) {
            Schema::create('unidad_curricular_prelacion', function (Blueprint $table) {
                $table->id('id_ucp');
                $table->unsignedBigInteger('mcd_id')->comment('Detalle de malla de la asignatura que tiene prelación');
                $table->unsignedBigInteger('mcd_prelacion_id')->comment('Detalle de malla de la asignatura requisito previo');
                $table->string('tipo_prelacion', 50)->default('OBLIGATORIA')->comment('OBLIGATORIA, CREDITOS, SIMULTANEA');
                $table->integer('creditos_requeridos')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('mcd_id')->references('id_mcd')->on('malla_curricular_detalle')->onDelete('cascade');
                $table->foreign('mcd_prelacion_id')->references('id_mcd')->on('malla_curricular_detalle')->onDelete('cascade');
            });
        }

        // 2. Sembrar Ejes Curriculares si está vacía
        if (Schema::hasTable('eje_curricular') && DB::table('eje_curricular')->count() === 0) {
            DB::table('eje_curricular')->insert([
                ['nombre_ecu' => 'Epistemológico / Metodológico', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nombre_ecu' => 'Socio-Crítico / Político', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nombre_ecu' => 'Técnico - Científico / Profesional', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nombre_ecu' => 'Estético - Lúdico / Acreditable', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nombre_ecu' => 'Proyecto / Integrador', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 3. Sembrar Tipos de Unidad Curricular si está vacía
        if (Schema::hasTable('tipo_unidad_curricular') && DB::table('tipo_unidad_curricular')->count() === 0) {
            DB::table('tipo_unidad_curricular')->insert([
                ['nombre_tuc' => 'Obligatoria', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nombre_tuc' => 'Proyecto Socio Tecnológico', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nombre_tuc' => 'Electiva Profesional', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nombre_tuc' => 'Acreditable / Taller', 'status' => true, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 4. Sembrar Unidades Curriculares para Informática si está vacía
        if (Schema::hasTable('unidad_curricular') && DB::table('unidad_curricular')->count() === 0) {
            $tucObl = DB::table('tipo_unidad_curricular')->where('nombre_tuc', 'Obligatoria')->value('id_tuc') ?? 1;
            $tucPst = DB::table('tipo_unidad_curricular')->where('nombre_tuc', 'like', '%Proyecto%')->value('id_tuc') ?? 2;
            $tucEle = DB::table('tipo_unidad_curricular')->where('nombre_tuc', 'like', '%Electiva%')->value('id_tuc') ?? 3;

            $materias = [
                ['codigo_unc' => 'INF-1101', 'nombre_unc' => 'Matemática I', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-1102', 'nombre_unc' => 'Algorítmica y Programación', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-1103', 'nombre_unc' => 'Arquitectura del Computador', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-1104', 'nombre_unc' => 'Proyecto Socio Tecnológico I', 'tuc_id' => $tucPst],
                ['codigo_unc' => 'INF-1201', 'nombre_unc' => 'Matemática II', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-1202', 'nombre_unc' => 'Estructuras de Datos', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-1203', 'nombre_unc' => 'Sistemas Operativos', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-2101', 'nombre_unc' => 'Bases de Datos', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-2102', 'nombre_unc' => 'Redes de Computadoras', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-2103', 'nombre_unc' => 'Ingeniería del Software I', 'tuc_id' => $tucObl],
                ['codigo_unc' => 'INF-2104', 'nombre_unc' => 'Proyecto Socio Tecnológico II', 'tuc_id' => $tucPst],
                ['codigo_unc' => 'INF-2201', 'nombre_unc' => 'Desarrollo Web y Frameworks', 'tuc_id' => $tucEle],
                ['codigo_unc' => 'INF-2202', 'nombre_unc' => 'Seguridad Informática', 'tuc_id' => $tucObl],
            ];

            foreach ($materias as $m) {
                DB::table('unidad_curricular')->insert([
                    'codigo_unc' => $m['codigo_unc'],
                    'nombre_unc' => $m['nombre_unc'],
                    'tuc_id'     => $m['tuc_id'],
                    'status'     => true,
                    'status_id'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Vincular a Malla Curricular Detalle si está vacía
        if (Schema::hasTable('malla_curricular_detalle') && DB::table('malla_curricular_detalle')->count() === 0) {
            $mallaId = DB::table('malla_curricular')->value('id_mac') ?? 1;
            $tra1 = DB::table('trayecto')->where('numero_orden_tra', 1)->value('id_tra') ?? 2;
            $tra2 = DB::table('trayecto')->where('numero_orden_tra', 2)->value('id_tra') ?? $tra1;
            $ecuTec = DB::table('eje_curricular')->where('nombre_ecu', 'like', '%Técnico%')->value('id_ecu') ?? 1;
            $ecuPro = DB::table('eje_curricular')->where('nombre_ecu', 'like', '%Proyecto%')->value('id_ecu') ?? 2;

            $ucs = DB::table('unidad_curricular')->orderBy('id_unc')->get();

            foreach ($ucs as $idx => $uc) {
                $isPst = str_contains($uc->nombre_unc, 'Proyecto');
                $isTra2 = $idx >= 7;

                DB::table('malla_curricular_detalle')->insert([
                    'mac_id'               => $mallaId,
                    'unc_id'               => $uc->id_unc,
                    'tra_id'               => $isTra2 ? $tra2 : $tra1,
                    'ecu_id'               => $isPst ? $ecuPro : $ecuTec,
                    'unidades_credito_mcd' => $isPst ? 6 : 3,
                    'htea_mcd'             => $isPst ? 4 : 3,
                    'htei_mcd'             => $isPst ? 6 : 3,
                    'thte_mcd'             => $isPst ? 10 : 6,
                    'duracion_semanas_mcd' => 36,
                    'status'               => true,
                    'status_id'            => 1,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        }

        // 6. Sembrar Prelaciones iniciales si está vacía
        if (Schema::hasTable('unidad_curricular_prelacion') && DB::table('unidad_curricular_prelacion')->count() === 0) {
            $getMcdId = function ($codigoUnc) {
                return DB::table('malla_curricular_detalle')
                    ->join('unidad_curricular', 'malla_curricular_detalle.unc_id', '=', 'unidad_curricular.id_unc')
                    ->where('unidad_curricular.codigo_unc', $codigoUnc)
                    ->value('malla_curricular_detalle.id_mcd');
            };

            $mcdMat1 = $getMcdId('INF-1101');
            $mcdMat2 = $getMcdId('INF-1201');
            $mcdAlgo = $getMcdId('INF-1102');
            $mcdEstr = $getMcdId('INF-1202');
            $mcdBd   = $getMcdId('INF-2101');
            $mcdIng  = $getMcdId('INF-2103');
            $mcdPst1 = $getMcdId('INF-1104');
            $mcdPst2 = $getMcdId('INF-2104');

            $prelaciones = [];
            if ($mcdMat1 && $mcdMat2) {
                $prelaciones[] = ['mcd_id' => $mcdMat2, 'mcd_prelacion_id' => $mcdMat1, 'tipo_prelacion' => 'OBLIGATORIA'];
            }
            if ($mcdAlgo && $mcdEstr) {
                $prelaciones[] = ['mcd_id' => $mcdEstr, 'mcd_prelacion_id' => $mcdAlgo, 'tipo_prelacion' => 'OBLIGATORIA'];
            }
            if ($mcdEstr && $mcdBd) {
                $prelaciones[] = ['mcd_id' => $mcdBd, 'mcd_prelacion_id' => $mcdEstr, 'tipo_prelacion' => 'OBLIGATORIA'];
            }
            if ($mcdEstr && $mcdIng) {
                $prelaciones[] = ['mcd_id' => $mcdIng, 'mcd_prelacion_id' => $mcdEstr, 'tipo_prelacion' => 'OBLIGATORIA'];
            }
            if ($mcdPst1 && $mcdPst2) {
                $prelaciones[] = ['mcd_id' => $mcdPst2, 'mcd_prelacion_id' => $mcdPst1, 'tipo_prelacion' => 'OBLIGATORIA'];
            }

            foreach ($prelaciones as $p) {
                DB::table('unidad_curricular_prelacion')->insert($p + [
                    'status'     => true,
                    'status_id'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidad_curricular_prelacion');
    }
};
