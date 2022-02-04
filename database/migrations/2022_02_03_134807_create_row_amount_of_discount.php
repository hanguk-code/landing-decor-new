<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRowAmountOfDiscount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oc_product', function (Blueprint $table) {
            $table->string('date_zakup')->nullable(true)->default(null);
            $table->string('amount_of_discount')->nullable(true)->default(null);
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
            $table->string('date_zakup')->nullable(true)->default(null);
            $table->string('amount_of_discount')->nullable(true)->default(null);
        });
    }
}
