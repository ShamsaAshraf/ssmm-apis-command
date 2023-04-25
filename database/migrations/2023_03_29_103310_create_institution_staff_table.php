<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institution_staff', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id');
            $table->integer('institution_id');
            $table->string('staff_type_name')->nullable();
            $table->integer('staff_status_id');
            $table->string('staff_status_name');
            $table->integer('institution_position_id');
            $table->string('institution_position_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_staff');
    }
};
