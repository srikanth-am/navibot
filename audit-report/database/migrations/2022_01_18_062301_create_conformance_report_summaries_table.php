<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConformanceReportSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conformance_report_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 255);
            $table->integer('order')->default(0);
            $table->string('wcag_version');
            $table->string('sc_name', 255)->unique();
            $table->string('level', 50);
            $table->integer('pass')->default(0);
            $table->integer('fail')->default(0);
            $table->integer('dna')->default(0);
            $table->integer('severity_low')->default(0);
            $table->integer('severity_medium')->default(0);
            $table->integer('severity_high')->default(0);
            $table->integer('severity_na')->default(0);
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
        Schema::dropIfExists('conformance_report_summaries');
    }
}
