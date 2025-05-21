<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID del usuario externo (desde auth)
            $table->unsignedBigInteger('enrollable_id');
            $table->string('enrollable_type');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();
        });
        
        
    }

    public function down()
    {
        Schema::dropIfExists('enrollments');
    }

};
