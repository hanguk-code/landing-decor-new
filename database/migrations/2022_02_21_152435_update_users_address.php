<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address2', 500)->nullable(true)->default(null);
            $table->string('address3', 500)->nullable(true)->default(null);
            $table->string('address4', 500)->nullable(true)->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address2', 500)->nullable(true)->default(null);
            $table->string('address3', 500)->nullable(true)->default(null);
            $table->string('address4', 500)->nullable(true)->default(null);
        });
    }
}
