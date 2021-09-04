<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('role_id');
            $table->foreignUuid('user_id');
            // $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('role_user')->insert([
            [
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
                'user_id' => '0effb42c-7369-4ced-960a-9aef46dadmin',
            ],
            [
                'role_id' => '0feb7d3a-90c0-42b9-be3f-63757088cb9a',
                'user_id' => 'b831b338-7a64-4c5e-a613-b83ddd9b133d',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}
