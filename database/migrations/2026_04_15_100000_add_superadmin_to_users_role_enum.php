<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MySQL: perluas ENUM role. SQLite (testing): kolom sudah cukup fleksibel; tidak diubah.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('employer', 'freelancer', 'superadmin') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')->where('role', 'superadmin')->update(['role' => 'employer']);

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('employer', 'freelancer') NOT NULL");
    }
};
