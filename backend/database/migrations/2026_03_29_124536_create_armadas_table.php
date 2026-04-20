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
        Schema::create('armada', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nopol', 50)->unique();
            $table->string('merk_armada_id');
            $table->string('jenis_armada_id');

            $table->foreign('merk_armada_id')
                ->references('id')->on('merk_armada')
                ->cascadeOnDelete();

            $table->foreign('jenis_armada_id')
                ->references('id')->on('jenis_armada')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('armadas');
    }
};
