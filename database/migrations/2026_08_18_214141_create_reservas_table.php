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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_lugares')->unsigned();
            $table->string('nombre', 100);
            $table->string('telefono', 50)->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->text('descripcion')->nullable();

            $table->foreign('id_lugares')->references('id')->on('lugares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
