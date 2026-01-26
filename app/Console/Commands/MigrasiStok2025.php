<?php

namespace App\Console\Commands;

use App\Imports\StokMultiSheetImport;
use App\Models\Category;
use App\Models\DetailPenerimaanBarang;
use App\Models\Penerimaan;
use App\Models\Satuan;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;

class MigrasiStok2025 extends Command
{
    protected $signature = 'app:migrasi-stok2025 {file}';
    protected $description = 'Migrasi stok awal dari excel multi sheet';

    public function handle()
    {
        DB::beginTransaction();

        try {
            $input = $this->argument('file');
            $input = str_replace('storage/app/', '', $input);
            $path = storage_path('app/' . $input);

            if (!file_exists($path)) {
                $this->error("File tidak ditemukan di: " . $path);
                return;
            }

            Excel::import(
                new StokMultiSheetImport(),
                $path
            );
            DB::commit();
            $this->info('Migrasi per kategori selesai!');

        } catch (\Exception $e) {

            DB::rollBack();
            $this->error('Gagal migrasi: ' . $e->getMessage());
        }
    }
}
