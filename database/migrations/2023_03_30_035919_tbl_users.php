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
        Schema::create('tbl_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->integer('school_ownership')->nullable();
            $table->string('identity_number')->nullable();
            $table->integer('moe_id')->nullable();
            $table->string('openemis_no')->nullable();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('third_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('position_name')->nullable();
            $table->integer('staff_position_title_id')->nullable();
            $table->integer('SSMM_ROLE')->nullable();
            $table->string('institution_name')->nullable();
            $table->integer('institution_code')->nullable();
            $table->string('area_name')->nullable();
            $table->integer('area_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
