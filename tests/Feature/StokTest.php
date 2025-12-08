<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Stok;
use App\Models\Satuan;
use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StokTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dapat_mengambil_daftar_stok()
    {
        $category = Category::factory()->create();
        $satuan = Satuan::factory()->create();

        Stok::factory()->count(3)->create([
            'category_id' => $category->id,
            'satuan_id' => $satuan->id
        ]);

        $response = $this->getJson('/api/v1/stok');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function dapat_mengambil_detail_stok()
    {
        $category = Category::factory()->create();
        $satuan = Satuan::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $category->id,
            'satuan_id' => $satuan->id,
        ]);

        $response = $this->getJson("/api/v1/stok/{$stok->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function dapat_update_stok()
    {
        $stok = Stok::factory()->create();

        $data = [
            'name' => 'Stok Baru',
            'minimum_stok' => 50
        ];

        $response = $this->putJson("/api/v1/stok/{$stok->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Stok berhasil diupdate',
            ]);

        $this->assertDatabaseHas('stoks', [
            'id' => $stok->id,
            'name' => 'Stok Baru',
            'minimum_stok' => 50,
        ]);
    }

    /** @test */
    public function dapat_mengambil_stok_paid()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        $penerimaan = Penerimaan::factory()->create([
            'status' => 'paid',
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        // barang wajib (supaya tidak error)
        DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaan->id
        ]);

        // pegawai wajib (supaya transformBastStock tidak error)
        DetailPenerimaanPegawai::factory()->create([
            'penerimaan_id' => $penerimaan->id
        ]);

        $response = $this->getJson('/api/v1/bast/paid');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data bast sudah dibayar berhasil diambil',
            ]);
    }

    /** @test */
    public function dapat_mengambil_stok_unpaid()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        $penerimaan = Penerimaan::factory()->create([
            'status' => 'signed',
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        // barang wajib
        DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaan->id
        ]);

        // pegawai wajib
        DetailPenerimaanPegawai::factory()->create([
            'penerimaan_id' => $penerimaan->id
        ]);

        $response = $this->getJson('/api/v1/bast/unpaid');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data bast belum dibayar berhasil diambil',
            ]);
    }
}
