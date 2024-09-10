<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id('trip_id');
            $table->dateTime('jadwal_perjalanan');
            $table->string('alamat_awal');
            $table->string('latitude_awal');
            $table->string('longitude_awal');
            $table->string('alamat_tujuan');
            $table->string('latitude_tujuan');
            $table->string('longitude_tujuan');
            $table->string('nama_kendaraan');
            $table->string('no_polisi');
            $table->enum('status', [
                'Belum Dimulai',
                'Dalam Perjalanan',
                'Selesai'
            ]);
            $table->string('trip_token');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('dimulai_pada')->nullable();
            $table->timestamp('diakhiri_pada')->nullable();
            $table->string('durasi_perjalanan')->nullable();
            $table->string('tinggi_badan_driver')->nullable();
            $table->string('berat_badan_driver')->nullable();
            $table->string('tekanan_darah_driver')->nullable();
            $table->text('riwayat_penyakit_driver')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
