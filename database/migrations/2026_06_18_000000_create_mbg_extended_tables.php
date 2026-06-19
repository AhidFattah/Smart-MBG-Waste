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
        // 1. Create Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name')->unique();
            $table->string('display_name');
            $table->timestamps();
        });

        // 2. Create Suppliers Table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 3. Add Columns to Users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
        });

        // 4. Add Columns to Inventories
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->decimal('harga', 12, 2)->default(0);
            $table->string('lokasi_penyimpanan')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->string('qr_code')->nullable();
            $table->string('foto_bahan')->nullable();
        });

        // 5. Create Inventory Logs Table
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->integer('sisa_stok_saat_itu')->default(0);
            $table->decimal('harga_satuan', 12, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // 6. Add Columns to Distributions
        Schema::table('distributions', function (Blueprint $table) {
            $table->integer('jumlah_siswa_hadir')->nullable();
            $table->decimal('persentase_cadangan', 5, 2)->default(3.00);
            $table->string('kendaraan_distribusi')->nullable();
            $table->string('petugas_distribusi')->nullable();
            $table->string('waktu_distribusi')->nullable();
            $table->string('qr_code_surat_jalan')->nullable();
            $table->timestamp('waktu_diterima')->nullable();
            $table->string('penerima_nama')->nullable();
        });

        // 7. Create Food Wastes Table
        Schema::create('food_wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('distributions')->onDelete('cascade');
            $table->decimal('berat_sisa_makanan', 8, 2); // dalam Kg
            $table->integer('jumlah_sisa_porsi')->default(0);
            $table->decimal('persentase_waste', 5, 2);
            $table->string('penyebab_waste')->nullable();
            $table->enum('kategori_waste', ['Sedikit', 'Sedang', 'Tinggi']);
            $table->timestamps();
        });

        // 8. Create Recommendations Table
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('tipe'); // portion, menu, stock
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->text('message');
            $table->timestamps();
        });

        // 9. Create Notifications Table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('tipe'); // info, warning, danger, success
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 10. Create Activity Logs Table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('aktivitas');
            $table->text('deskripsi')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // 11. Create Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('umum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('food_wastes');

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_siswa_hadir', 'persentase_cadangan',
                'kendaraan_distribusi', 'petugas_distribusi',
                'waktu_distribusi', 'qr_code_surat_jalan',
                'waktu_diterima', 'penerima_nama'
            ]);
        });

        Schema::dropIfExists('inventory_logs');

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'harga', 'lokasi_penyimpanan', 'barcode', 'qr_code', 'foto_bahan']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id']);
        });

        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('roles');
    }
};
