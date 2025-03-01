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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('material')->nullable()->change();
            $table->float('height')->nullable()->change();
            $table->float('width')->nullable()->change();
            $table->float('depth')->nullable()->change();
            $table->string('units')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('material')->nullable(false)->change();
            $table->float('height')->nullable(false)->change();
            $table->float('width')->nullable(false)->change();
            $table->float('depth')->nullable(false)->change();
            $table->string('units')->nullable(false)->change();
        });
    }
};
