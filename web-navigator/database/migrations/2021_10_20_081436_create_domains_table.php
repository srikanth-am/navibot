<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            //
            $table->string('url', 255)->unique();
            $table->integer('total_urls')->default(0);
            $table->integer('total_sitemaps')->default(0);
            $table->dateTime('s_time', 0)->nullable();
            $table->dateTime('e_time', 0)->nullable();
            $table->dateTime('t_utilized', 0)->nullable();
            $table->integer('status')->default(0);
            $table->integer('url_status')->default(0);
            $table->integer('temp_status')->default(0);
            $table->integer('url_progress')->default(0);
            $table->integer('temp_progress')->default(0);
            $table->string('current_progress', 150)->nullable();
            $table->integer('http_status')->default(0);
            //
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
        Schema::dropIfExists('domains');
    }
}