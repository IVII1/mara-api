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
        Schema::table('images', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('material')->nullable();
            $table->float('height')->nullable();
            $table->float('width')->nullable();
            $table->float('depth')->nullable(); 
            $table->string('units')->nullable();
            $table->integer('production_year')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('material');
            $table->dropColumn('height');
            $table->dropColumn('width');
            $table->dropColumn('depth'); 
            $table->dropColumn('units');
            $table->dropColumn('production_year');
            $table->dropColumn('description');
        });
    }
};
