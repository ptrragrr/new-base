<?php
// database/seeders/RolePermissionSeeder.php

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Buat permission kalau belum ada
        Permission::firstOrCreate(['name' => 'tambah-kategori']);

        $this->call(RolePermissionSeeder::class);
        
        // Cari user yang mau dikasih (contoh admin pertama)
        $user = User::where('email', 'admin@example.com')->first();

        if ($user) {
            $user->givePermissionTo('tambah-kategori');
        }
    }
}
