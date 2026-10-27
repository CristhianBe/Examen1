<?php

use App\Models\Categoria;
use App\Models\Equipo;
use App\Models\User;
use Laravel\Sanctum\Sanctum;


beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

it('crea un equipo y responde 201 con el encabezado Location', function () {
    $categoria = Categoria::factory()->create();

    $respuesta = $this->postJson('/api/equipos', [
        'codigo'            => 'EQ-9001',
        'nombre'            => 'Laptop de prueba',
        'categoria_id'      => $categoria->id,
        'fecha_adquisicion' => now()->subDay()->format('Y-m-d'),
        'costo_interno'     => 350000,
    ]);

    $equipo = Equipo::where('codigo', 'EQ-9001')->first();

    $respuesta->assertCreated()
        ->assertHeader('Location', route('equipos.show', $equipo))
        ->assertJsonPath('data.codigo', 'EQ-9001')
        ->assertJsonPath('data.estado', 'disponible')
        ->assertJsonPath('data.categoria.id', $categoria->id)
        ->assertJsonMissingPath('data.costo_interno');

    $this->assertDatabaseHas('equipos', ['codigo' => 'EQ-9001']);
});

it('rechaza un equipo con datos inválidos y responde 422 con errores por campo', function () {
    $this->postJson('/api/equipos', [
        'codigo'            => 'ABC',
        'nombre'            => 'X',
        'categoria_id'      => 9999,
        'estado'            => 'prestado',
        'fecha_adquisicion' => now()->addYear()->format('Y-m-d'),
        'costo_interno'     => -5,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'codigo', 'nombre', 'categoria_id', 'estado', 'fecha_adquisicion', 'costo_interno',
        ])
        ->assertJsonPath('errors.codigo.0', 'El codigo debe tener el formato EQ-0000.');

    $this->assertDatabaseCount('equipos', 0);
});

it('impide eliminar un equipo prestado y responde 409', function () {
    $equipo = Equipo::factory()->prestado()->create();

    $this->deleteJson("/api/equipos/{$equipo->id}")
        ->assertConflict()
        ->assertExactJson([
            'mensaje' => 'No se puede eliminar un equipo prestado.',
            'codigo'  => 'REGLA_NEGOCIO',
        ]);

    $this->assertDatabaseHas('equipos', ['id' => $equipo->id, 'estado' => 'prestado']);
});
