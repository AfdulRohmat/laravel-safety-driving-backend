<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', [
                'ROLE_DRIVER',
                'ROLE_COMPANY',
                'ROLE_FAMILY',
                'ROLE_MEDIC',
                'ROLE_KNKT',
                'ROLE_USER_GROUP',
                'ROLE_ADMIN_GROUP',
            ]);
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
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
        Schema::dropIfExists('group_members');
    }
}
