<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AM\MasterCity;
use Illuminate\Support\Facades\File;

class MasterCitySeeder extends Seeder
{
    public function run()
    {
        // Pastikan path file CSV sesuai dengan lokasi file Anda
        // Misalnya file diletakkan di folder database/seeders/city_list_indonesia.csv
        $csvFile = database_path('seeders/city_list_indonesia.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan di: $csvFile");
            return;
        }

        $data = array_map('str_getcsv', file($csvFile));
        $header = array_shift($data); // Ambil baris header (ID, City, Province, Nation)

        foreach ($data as $row) {
            // Sesuaikan indeks array dengan kolom CSV Anda:
            // 0 -> ID
            // 1 -> City
            // 2 -> Province
            // 3 -> Nation

            if (count($row) >= 4) {
                MasterCity::create([
                    'city_name' => $row[1], // Kolom City
                    'province'  => $row[2], // Kolom Province
                    'nation'    => $row[3], // Kolom Nation
                ]);
            }
        }
    }
}
