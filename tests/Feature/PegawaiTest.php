<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dapat_mengambil_daftar_pegawai()
    {
        $jabatan = Jabatan::factory()->create();
        Pegawai::factory()->count(3)->create(['jabatan_id' => $jabatan->id]);

        $response = $this->getJson('/api/v1/pegawai');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'nip', 'jabatan_id', 'status']
                    ]
                ]
            ]);
    }

    /** @test */
    public function dapat_menambah_pegawai_baru()
    {
        $jabatan = Jabatan::factory()->create();

        $data = [
            'name' => 'Almas Teva',
            'nip' => '19890504',
            'jabatan_id' => $jabatan->id,
            'phone' => '081234567890',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/pegawai', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pegawai berhasil ditambahkan',
            ]);

        $this->assertDatabaseHas('pegawais', ['nip' => '19890504']);
    }

    /** @test */
    public function validasi_gagal_jika_field_kosong()
    {
        $response = $this->postJson('/api/v1/pegawai', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'nip', 'jabatan_id', 'phone']);
    }

    /** @test */
    public function dapat_mengubah_status_pegawai()
    {
        $jabatan = Jabatan::factory()->create();
        $pegawai = Pegawai::factory()->create([
            'jabatan_id' => $jabatan->id,
            'status' => 'active'
        ]);

        $response = $this->patchJson("/api/v1/pegawai/{$pegawai->id}/status");

        $response->assertStatus(200);
        $this->assertDatabaseHas('pegawais', [
            'id' => $pegawai->id,
            'status' => 'inactive'
        ]);
    }
}
