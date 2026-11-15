<?php

use App\Models\Personas\Persona;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

it('stores a student with the new registration fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('personas.store'), [
        'per_cedula' => '12345678',
        'per_documento' => '12345678',
        'per_tipo_documento' => 'E',
        'per_primer_nombre' => 'Ana',
        'per_segundo_nombre' => 'María',
        'per_primer_apellido' => 'Pérez',
        'per_segundo_apellido' => 'García',
        'per_genero' => 'M',
        'per_edo_civil' => 'S',
        'per_fecha_nacimiento' => '2000-01-01',
        'per_email_local' => 'ana',
        'per_email_domain' => '@gmail.com',
        'per_telefono_movil_codigo' => '0412',
        'per_telefono_movil_numero' => '1234567',
        'per_telefono_fijo_codigo' => '0212',
        'per_telefono_fijo_numero' => '7654321',
        'per_telefono_trabajo_codigo' => '0416',
        'per_telefono_trabajo_numero' => '1111111',
        'per_pais_nacimiento' => 'Venezuela',
        'per_estatus' => 'A',
    ]);

    $response->assertRedirect(route('personas.index'));

    $persona = Persona::find('12345678');

    expect($persona)->not->toBeNull()
        ->and($persona->per_tipo_documento)->toBe('E')
        ->and($persona->per_nombres)->toBe('Ana María')
        ->and($persona->per_apellidos)->toBe('Pérez García')
        ->and($persona->per_email)->toBe('ana@gmail.com')
        ->and($persona->per_telefono_movil)->toBe('04121234567')
        ->and($persona->per_telefono_fijo)->toBe('02127654321')
        ->and($persona->per_telefono_trabajo)->toBe('04161111111');
});

it('stores a student with uploaded documents', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    // Get a document code from the database
    $doc = DB::table('documento')->first();
    expect($doc)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('personas.store'), [
        'per_cedula' => '12345678',
        'per_documento' => '12345678',
        'per_tipo_documento' => 'E',
        'per_primer_nombre' => 'Ana',
        'per_segundo_nombre' => 'María',
        'per_primer_apellido' => 'Pérez',
        'per_segundo_apellido' => 'García',
        'per_genero' => 'M',
        'per_edo_civil' => 'S',
        'per_fecha_nacimiento' => '2000-01-01',
        'per_email_local' => 'ana',
        'per_email_domain' => '@gmail.com',
        'per_telefono_movil_codigo' => '0412',
        'per_telefono_movil_numero' => '1234567',
        'per_pais_nacimiento' => 'Venezuela',
        'per_estatus' => 'A',
        'documentos_consignados_files' => [
            $doc->doc_codigo => UploadedFile::fake()->create('voucher.pdf', 150)
        ]
    ]);

    $response->assertRedirect(route('personas.index'));

    // Assert that the file is stored in public storage under personas/documentos
    $consignado = DB::table('documentos_consignados')
        ->where('dco_cedula', '12345678')
        ->where('dco_cod_documento', $doc->doc_codigo)
        ->first();

    expect($consignado)->not->toBeNull();
    expect($consignado->dco_archivo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($consignado->dco_archivo_path);
});

it('stores a docente with uploaded documents', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    // Get a docente document code from the database
    $doc = DB::table('documento')->where('doc_tipo', 'D')->first();
    expect($doc)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('personas.store'), [
        'per_cedula' => '87654321',
        'per_documento' => '87654321',
        'per_tipo_documento' => 'V',
        'per_primer_nombre' => 'Carlos',
        'per_segundo_nombre' => 'José',
        'per_primer_apellido' => 'Mendoza',
        'per_segundo_apellido' => 'Gómez',
        'per_genero' => 'H',
        'per_edo_civil' => 'C',
        'per_fecha_nacimiento' => '1985-05-15',
        'per_email_local' => 'carlos',
        'per_email_domain' => '@gmail.com',
        'per_telefono_movil_codigo' => '0414',
        'per_telefono_movil_numero' => '7654321',
        'per_pais_nacimiento' => 'Venezuela',
        'per_estatus' => 'A',
        'doc_cod_profesion' => 'Educacion',
        'doc_cod_categoria_inicial' => 'Asistente',
        'documentos_consignados_files' => [
            $doc->doc_codigo => UploadedFile::fake()->create('titulo_pregrado.pdf', 500)
        ]
    ]);

    $response->assertRedirect(route('personas.index'));

    // Assert that the file is stored in public storage and logged in DB
    $consignado = DB::table('documentos_consignados')
        ->where('dco_cedula', '87654321')
        ->where('dco_cod_documento', $doc->doc_codigo)
        ->first();

    expect($consignado)->not->toBeNull();
    expect($consignado->dco_archivo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($consignado->dco_archivo_path);
});
