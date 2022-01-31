<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitemapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sitemaps', function (Blueprint $table) {
            $table->id();
            //
            $table->string('url', 255)->unique();
            $table->foreignId('domain_id')->nullable()->constrained('domains');
            $table->tinyInteger('is_crawled')->default('0');
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
        Schema::dropIfExists('sitemaps');
    }
}