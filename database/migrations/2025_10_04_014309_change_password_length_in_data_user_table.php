<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dataUser', function (Blueprint $table) {
            // Change the password column to have a length of 255 characters
            $table->string('password', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dataUser', function (Blueprint $table) {
            // Revert the change. Note: This might fail if the original length was different
            // and there are now long strings in the column.
            $table->string('password')->change();
        });
    }
};