<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('permission_value', 191);
            $table->string('permission_name', 191);
            $table->timestamps();
        });

        DB::table('permissions')->insert(
            [
                'id' => '7c0dcd38-abf2-4182-8ea2-f23831188f91',
                'permission_value' => 'dashboard:read',
                'permission_name' => 'Access dashboard menu',
            ],
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
