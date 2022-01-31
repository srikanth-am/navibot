<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            //
            $table->foreignId('domain_id')->nullable()->constrained('domains');
            $table->string('url', 255);
            $table->string('type')->nullable();
            $table->integer('template')->nullable();
            $table->tinyInteger('is_crawled')->default('0');
            $table->integer('http_status')->default(200);
            //
            $table->unique(["domain_id", "url", "type"], 'unique_url');
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
        Schema::dropIfExists('urls');
    }
}