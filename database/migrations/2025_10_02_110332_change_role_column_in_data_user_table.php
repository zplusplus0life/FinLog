<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dataUser', function (Blueprint $table) {
            // Change the 'role' column to a string, then add a check constraint.
            $table->string('role', 255)->default('staff')->change();
        });
    
           if (DB::getDriverName() !== 'sqlite') {
        DB::statement("
            ALTER TABLE `dataUser`
            ADD CONSTRAINT `dataUser_role_check`
            CHECK (role IN ('admin', 'manajer', 'staff'))
        ");
    }
    
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      if (DB::getDriverName() !== 'sqlite') {
        DB::statement('
            ALTER TABLE `dataUser`
            DROP CONSTRAINT IF EXISTS `dataUser_role_check`
        ');
    }

    Schema::table('dataUser', function (Blueprint $table) {
        $table->string('role')->default('staff')->change();
    });
    }
};