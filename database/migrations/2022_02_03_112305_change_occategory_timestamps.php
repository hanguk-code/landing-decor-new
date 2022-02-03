<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOccategoryTimestamps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oc_category', function (Blueprint $table) {
            $table->string('date_added')->nullable(true)->default(null)->change();
            $table->string('date_modified')->nullable(true)->default(null)->change();
            $table->binary('seo_text')->nullable(true)->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oc_category', function (Blueprint $table) {
            $table->string('date_added')->nullable(true)->default(null)->change();
            $table->string('date_modified')->nullable(true)->default(null)->change();
            $table->binary('seo_text')->nullable(true)->default(null);
        });
    }
}
