<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_users', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('nama_depan');
            $table->string('nama_belakang');
            $table->string('no_telepon');
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan']); // Define possible values
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_users');
    }
}
