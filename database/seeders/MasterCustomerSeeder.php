<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AM\MasterCustomer;
use Illuminate\Support\Facades\File;

class MasterCustomerSeeder extends Seeder
{
    public function run()
    {
        // Sesuaikan path file CSV Anda
        $csvFile = database_path('seeders/customer.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan di: $csvFile");
            return;
        }

        // Baca CSV menggunakan str_getcsv
        $data = array_map('str_getcsv', file($csvFile));
        $header = array_shift($data); // Lewati baris header

        foreach ($data as $row) {
            // Pastikan baris memiliki cukup kolom (minimal 4 kolom utama)
            if (count($row) > 3) {
                // Mapping Kolom berdasarkan file CSV:
                // Indeks 0: Kode
                // Indeks 1: Nama Customer
                // Indeks 2: Alamat
                // Indeks 3: Telepon

                // Gunakan updateOrCreate agar tidak duplikat saat seeder dijalankan ulang
                MasterCustomer::updateOrCreate(
                    ['kode' => $row[0]], // Cek berdasarkan Kode
                    [
                        'nama'    => $row[1],
                        'alamat'  => $row[2],
                        'telepon' => $row[3],
                    ]
                );
            }
        }
    }
}
