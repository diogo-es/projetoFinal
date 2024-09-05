<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agregadora_agregada', function (Blueprint $table) {
            $table->unsignedBigInteger('id_agregadora');
            $table->unsignedBigInteger('id_agregada');

            // Define the composite primary key
            $table->primary(['id_agregadora', 'id_agregada']);

            // Define foreign keys
            $table->foreign('id_agregadora')->references('id')->on('unidades_curriculares_agregadoras')->onDelete('cascade');
            $table->foreign('id_agregada')->references('id')->on('unidades_curriculares')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agregadora_agregada');
    }
};
