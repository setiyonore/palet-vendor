<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PanelAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Membuat permission untuk akses panel
        // 'web' adalah guard default untuk login user
        Permission::findOrCreate('access_panel_admin', 'web');
        Permission::findOrCreate('access_panel_armada', 'web');

        $this->command->info('Permission akses panel berhasil dibuat.');
    }
}
