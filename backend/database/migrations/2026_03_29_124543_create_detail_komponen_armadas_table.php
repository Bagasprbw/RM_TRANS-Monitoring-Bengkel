<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("detail_komponen_armada", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("komponen_armada_id");

            // KHUSUS BAN (Sheet 6)
            $table->string("nomor_seri", 50)->nullable(); // khususu NO SERI ban dan accu
            $table->string("nomor_stamp", 50)->nullable(); // NO STAMP ban
            $table
                ->enum("jenis_ban", ["ORI", 'VULK PEMBELIAN, "VULK JASA'])
                ->nullable(); // ORI / VULK
            $table->string("ukuran", 50)->nullable(); // Ukuran ban
            $table->string("merk_tipe", 100)->nullable(); // Merk / Type ban
            $table->string("pemasok", 100)->nullable(); // Supplier
            $table->decimal("harga", 12, 2)->nullable(); // Harga ban
            $table->date("tanggal_pemasangan")->nullable(); // KHUSUS: Ban, Accu, Filter Udara
            $table->integer("km_pemasangan")->nullable(); // KHUSUS: Ban (SPEDOMETER PEMASANGAN)
            $table->date("tanggal_pelepasan")->nullable(); // KHUSUS: Ban (TGL LEPAS)
            $table->enum("status_ban_bekas", ["VULK", "JUAL"])->nullable(); // KHUSUS: Ban (VULK/JUAL)

            // SEMUA KOMPONEN
            $table->text("keterangan")->nullable(); // Catatan tambahan (semua komponen)

            $table
                ->foreign("komponen_armada_id")
                ->references("id")
                ->on("komponen_armada")
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("detail_komponen_armada");
    }
};
