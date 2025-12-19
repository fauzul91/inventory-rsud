<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\Category;
use App\Models\Satuan;
use App\Models\Stok;
use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use Illuminate\Support\Facades\DB;

class PelaporanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dapat_mengambil_data_dashboard()
    {
        $user     = User::factory()->create();
        $kategori = Category::factory()->create();
        $satuan   = Satuan::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        $penerimaanSigned = Penerimaan::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $kategori->id,
            'status'      => 'signed',
        ]);

        $detailPenerimaanMasuk = DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaanSigned->id,
            'stok_id'       => $stok->id,
            'quantity'      => 10,
            'harga'         => 1000,
            'total_harga'   => 10000,
        ]);

        $pemesanan = Pemesanan::factory()->create([
            'user_id'           => $user->id,
            'nama_pj_instalasi' => 'TEST',
            'ruangan'           => 'TEST',
            'status'            => 'pending',
            'tanggal_pemesanan' => now()->toDateString(),
        ]);

        $detailPemesanan = DetailPemesanan::factory()->create([
            'pemesanan_id' => $pemesanan->id,
            'stok_id'      => $stok->id,
            'quantity'     => 4,
        ]);

        DB::table('detail_pemesanan_penerimaan')->insert([
            'detail_pemesanan_id'  => $detailPemesanan->id,
            'detail_penerimaan_id' => $detailPenerimaanMasuk->id,
            'quantity'             => 4,
            'harga'                => 1000,
            'subtotal'             => 4000,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

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
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_stok_barang'    => 13,
                    'bast_sudah_diterima'  => 1,
                    'barang_belum_dibayar' => 17,
                ],
            ]);
    }

    /** @test */
    public function dapat_mengambil_penerimaan_per_bulan()
    {
        $user     = User::factory()->create();
        $kategori = Category::factory()->create();
        $satuan   = Satuan::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        $penerimaan = Penerimaan::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $kategori->id,
        ]);

        DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaan->id,
            'stok_id'       => $stok->id,
            'quantity'      => 10,
            'harga'         => 1000,
            'total_harga'   => 10000,
            'created_at'    => '2024-05-10 10:00:00',
        ]);

        $response = $this->getJson('/api/v1/pelaporan/penerimaan-per-bulan?year=2024');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount(12, $data);

        $mei = collect($data)->firstWhere('month', 5);
        $this->assertEquals(10, $mei['total']);
    }

    /** @test */
    public function dapat_mengambil_pengeluaran_per_bulan()
    {
        $user     = User::factory()->create();
        $kategori = Category::factory()->create();
        $satuan   = Satuan::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        $penerimaan = Penerimaan::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $kategori->id,
        ]);

        $detailPenerimaan = DetailPenerimaanBarang::factory()->create([
            'penerimaan_id' => $penerimaan->id,
            'stok_id'       => $stok->id,
            'quantity'      => 10,
            'harga'         => 1000,
            'total_harga'   => 10000,
        ]);

        $pemesanan = Pemesanan::factory()->create([
            'user_id'           => $user->id,
            'nama_pj_instalasi' => 'TEST',
            'ruangan'           => 'TEST',
            'status'            => 'approved_admin_gudang',
            'tanggal_pemesanan' => '2024-08-01',
        ]);

        $detailPemesanan = DetailPemesanan::factory()->create([
            'pemesanan_id' => $pemesanan->id,
            'stok_id'      => $stok->id,
            'quantity'     => 7,
        ]);

        DB::table('detail_pemesanan_penerimaan')->insert([
            'detail_pemesanan_id'  => $detailPemesanan->id,
            'detail_penerimaan_id' => $detailPenerimaan->id,
            'quantity'             => 7,
            'harga'                => 2000,
            'subtotal'             => 14000,
            'created_at'           => '2024-08-20 09:00:00',
            'updated_at'           => '2024-08-20 09:00:00',
        ]);

        $response = $this->getJson('/api/v1/pelaporan/pengeluaran-per-bulan?year=2024');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount(12, $data);

        $agustus = collect($data)->firstWhere('month', 8);
        $this->assertEquals(7, $agustus['total']);
    }
}
