<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOcproductsTimestamps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oc_product', function (Blueprint $table) {
            $table->string('date_added')->nullable(true)->default(null)->change();
            $table->string('date_modified')->nullable(true)->default(null)->change();
			$table->string('upc_date')->nullable(true)->default(null)->change();
			$table->string('date_available')->nullable(true)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oc_product', function (Blueprint $table) {
            $table->string('date_added')->nullable(true)->default(null)->change();
            $table->string('date_modified')->nullable(true)->default(null)->change();
			$table->string('upc_date')->nullable(true)->default(null)->change();
			$table->string('date_available')->nullable(true)->default(null)->change();
        });
    }
}
