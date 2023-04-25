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
        Schema::create('institution_shifts', function (Blueprint $table) {
            $table->id();
            $table->integer('shift_id');
            $table->integer('institution_id');
            $table->string('shift_option_id');
            $table->string('shift_option_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_shifts');
    }
};
