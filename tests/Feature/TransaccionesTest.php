<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transacciones;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransaccionesTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function no_permite_superar_limite_diario()
    {
        $user = User::factory()->create();

        // Crear una transacción cercana al límite
        Transacciones::create([
            'user_id' => $user->id,
            'monto' => 4000,
            'fecha' => now()->format('Y-m-d H:i:s'),
        ]);

        // Intentar crear otra que supere el límite
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/transacciones', [
                'user_id' => $user->id,
                'monto' => 2000,
                'fecha' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Límite diario de transferencia alcanzado (5.000 USD)'
            ]);
    }

    /** @test */
    public function no_permite_transacciones_duplicadas()
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'monto' => 1500,
            'fecha' => now()->format('Y-m-d H:i:s'),
        ];

        // Crear transacción
        Transacciones::create($data);

        // Intentar crear duplicada
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/transacciones', $data);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Transacción duplicada detectada'
            ]);
    }

    /** @test */
    public function permite_transaccion_valida()
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'monto' => 1000,
            'fecha' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/transacciones', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'user_id', 'monto', 'fecha', 'created_at', 'updated_at'
                ]
            ]);
    }
}
