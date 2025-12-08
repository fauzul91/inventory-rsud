<?php

namespace Tests\Feature;

use App\Models\Pemesanan;
use App\Models\User;
use App\Models\DetailPemesanan;
use App\Models\Stok;
use App\Models\Category;
use App\Models\Satuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemesananTest extends TestCase
{
    use RefreshDatabase;

    private string $base = "/api/v1/pemesanan";

    /** @test */
    public function dapat_mengambil_daftar_pemesanan()
    {
        $user = User::factory()->create();

        Pemesanan::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        $response = $this->getJson($this->base);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    /** @test */
    public function dapat_menambah_pemesanan_baru()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create();
        $satuan   = Satuan::factory()->create();
        $stok     = Stok::factory()->create([
            'category_id' => $category->id,
            'satuan_id'   => $satuan->id,
        ]);

        $payload = [
            'nama_pj_instalasi' => 'dr. Ahmad',
            'ruangan' => 'ICU',
            'items' => [
                [
                    'stok_id' => $stok->id,
                    'quantity' => 5
                ]
            ]
        ];

        $response = $this->postJson($this->base, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data pemesanan berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('pemesanans', [
            'nama_pj_instalasi' => 'dr. Ahmad'
        ]);
    }

    /** @test */
    public function validasi_gagal_jika_field_wajib_kosong()
    {
        $response = $this->postJson($this->base, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'nama_pj_instalasi',
                'ruangan',
                'items',
            ]);
    }

    /** @test */
    public function dapat_mengambil_detail_pemesanan()
    {
        $p = Pemesanan::factory()->create();

        $response = $this->getJson("{$this->base}/{$p->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Detail pemesanan berhasil diambil'
            ]);
    }

    /** @test */
    public function mengembalikan_500_jika_pemesanan_tidak_ditemukan()
    {
        $response = $this->getJson("{$this->base}/999");

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ]);
    }
}




    /** @test */
    // public function dapat_update_quantity_detail_pemesanan()
    // {
    //     $p = Pemesanan::factory()->create();

    //     $category = Category::factory()->create();
    //     $satuan   = Satuan::factory()->create();
    //     $stok     = Stok::factory()->create([
    //         'category_id' => $category->id,
    //         'satuan_id'   => $satuan->id,
    //     ]);

    //     $detail = DetailPemesanan::factory()->create([
    //         'pemesanan_id' => $p->id,
    //         'stok_id'      => $stok->id,
    //         'quantity' => 3
    //     ]);

    //     $response = $this->patchJson("{$this->base}/{$p->id}/detail/{$detail->id}/quantity", [
    //         'quantity' => 7
    //     ]);

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'success' => true,
    //             'message' => 'Quantity berhasil diperbarui',
    //         ]);

    //     $this->assertDatabaseHas('detail_pemesanans', [
    //         'id' => $detail->id,
    //         'quantity' => 10
    //     ]);
    // }
