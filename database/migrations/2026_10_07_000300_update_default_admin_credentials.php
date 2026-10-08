<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $oldAdmin = DB::table('users')->where('email', 'admin@demo.polteksi.ac.id')->where('role', 'admin')->first();
        $newAdminExists = DB::table('users')->where('email', 'admin@polteksi.ac.id')->exists();

        if ($oldAdmin && ! $newAdminExists) {
            DB::table('users')->where('id', $oldAdmin->id)->update([
                'email' => 'admin@polteksi.ac.id',
                'password' => Hash::make('polteksi123'),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $admin = DB::table('users')->where('email', 'admin@polteksi.ac.id')->where('role', 'admin')->first();
        $oldAdminExists = DB::table('users')->where('email', 'admin@demo.polteksi.ac.id')->exists();

        if ($admin && ! $oldAdminExists) {
            DB::table('users')->where('id', $admin->id)->update([
                'email' => 'admin@demo.polteksi.ac.id',
                'password' => Hash::make('demo12345'),
                'updated_at' => now(),
            ]);
        }
    }
};
