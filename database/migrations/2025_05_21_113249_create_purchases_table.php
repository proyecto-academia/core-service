<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            // user_id no FK porque users están en otro microservicio
            $table->unsignedBigInteger('user_id');

            // Relación a enrollment
            $table->unsignedBigInteger('enrollment_id')->nullable();

            $table->decimal('amount', 8, 2);
            $table->string('payment_method');
            $table->string('status'); // ejemplo: pending, completed, failed

            $table->timestamps();

            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
