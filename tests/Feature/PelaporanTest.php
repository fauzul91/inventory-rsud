<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\Category;
use App\Models\Satuan;
use App\Models\Stok;
use App\Models\StokHistory;
use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;

class PelaporanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dapat_mengambil_data_dashboard()
    {
        $satuan   = Satuan::factory()->create();
        $kategori = Category::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        // stok history buat total stok barang
        StokHistory::create([
            'stok_id'       => $stok->id,
            'year'          => now()->year,
            'quantity'      => 10,
            'used_qty'      => 2,
            'remaining_qty' => 8,
            'source'        => 'test',
            'source_id'     => 1,
        ]);

        // user & penerimaan signed
        $user = User::factory()->create();

        $penerimaanSigned = Penerimaan::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $kategori->id,
            'status'      => 'signed',
        ]);

        DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaanSigned->id,
            'stok_id'       => $stok->id,
            'quantity'      => 5,
            'harga'         => 1000,
            'total_harga'   => 5000,
        ]);

        // penerimaan belum dibayar
        $penerimaanBelumBayar = Penerimaan::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $kategori->id,
            'status'      => 'checked',
        ]);

        DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaanBelumBayar->id,
            'stok_id'       => $stok->id,
            'quantity'      => 7,
            'harga'         => 2000,
            'total_harga'   => 14000,
            'is_paid'       => null,
        ]);

        $response = $this->getJson('/api/v1/pelaporan/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_stok_barang',
                    'bast_sudah_diterima',
                    'barang_belum_dibayar',
                ],
            ]);
    }

    /** @test */
    public function dapat_mengambil_penerimaan_per_bulan()
    {
        $satuan   = Satuan::factory()->create();
        $kategori = Category::factory()->create();
        $user     = User::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        $penerimaan = Penerimaan::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $kategori->id,
            // created_at default now()
        ]);

        // Data dummy di bulan Mei 2024
        DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaan->id,
            'stok_id'       => $stok->id,
            'quantity'      => 10,
            'harga'         => 1000,
            'total_harga'   => 10000,
            'created_at'    => '2024-05-10 00:00:00',
        ]);

        $response = $this->getJson('/api/v1/pelaporan/penerimaan-per-bulan?year=2024');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['month', 'total'],
                ],
            ]);

        $data = $response->json('data');

        // 12 bulan
        $this->assertCount(12, $data);

        foreach ($data as $row) {
            $this->assertGreaterThanOrEqual(1, $row['month']);
            $this->assertLessThanOrEqual(12, $row['month']);
            $this->assertArrayHasKey('total', $row);
        }
    }

    /** @test */
    public function dapat_mengambil_pengeluaran_per_bulan()
    {
        $satuan   = Satuan::factory()->create();
        $kategori = Category::factory()->create();
        $user     = User::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        $pemesanan = Pemesanan::factory()->create([
            'user_id'            => $user->id,
            'status'             => 'done',
            'tanggal_pemesanan'  => '2024-08-01',
        ]);

        DetailPemesanan::factory()->create([
            'pemesanan_id' => $pemesanan->id,
            'stok_id'      => $stok->id,
            'quantity'     => 7,
            'created_at'   => '2024-08-20 00:00:00',
        ]);

        $response = $this->getJson('/api/v1/pelaporan/pengeluaran-per-bulan?year=2024');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['month', 'total'],
                ],
            ]);

        $data = $response->json('data');

        // 12 bulan
        $this->assertCount(12, $data);

        foreach ($data as $row) {
            $this->assertGreaterThanOrEqual(1, $row['month']);
            $this->assertLessThanOrEqual(12, $row['month']);
            $this->assertArrayHasKey('total', $row);
        }
    }
}
