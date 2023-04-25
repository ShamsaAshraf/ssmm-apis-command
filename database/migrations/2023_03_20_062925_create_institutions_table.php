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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->integer('institutionId')->nullable();
            $table->integer('shiftId')->nullable();
            $table->string('institution_name')->nullable();
            $table->string('alternative_name')->nullable();
            $table->string('institution_code')->nullable();
            $table->string('address')->nullable();
            $table->integer('postal_code')->nullable();
            $table->string('date_opened')->nullable();
            $table->integer('year_opened')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->integer('area_id')->nullable();
            $table->string('area_name')->nullable();
            $table->integer('area_administrative_id')->nullable();
            $table->string('area_administrative_name')->nullable();
            $table->string('institution_locality_name')->nullable();
            $table->string('institution_locality_international_code')->nullable();
            $table->string('institution_locality_national_code')->nullable();
            $table->integer('institution_classification_id')->nullable();
            $table->string('institution_classification_name')->nullable();
            $table->integer('institution_type_id')->nullable();
            $table->string('institution_type_name')->nullable();
            $table->string('institution_type_national_code')->nullable();
            $table->integer('institution_ownership_id')->nullable();
            $table->string('institution_ownership_international_code')->nullable();
            $table->string('institution_ownership_national_code')->nullable();
            $table->integer('institution_status_id')->nullable();
            $table->string('institution_status_name')->nullable();
            $table->string('institution_status_code')->nullable();
            $table->integer('institution_sector_id')->nullable();
            $table->string('institution_sector_name')->nullable();
            $table->string('institution_sector_international_code')->nullable();
            $table->string('institution_sector_national_code')->nullable();
            $table->integer('institution_provider_id')->nullable();
            $table->string('institution_provider_name')->nullable();
            $table->string('institution_provider_international_code')->nullable();
            $table->string('institution_provider_national_code')->nullable();
            $table->integer('institution_gender_id')->nullable();
            $table->string('institution_gender_name')->nullable();
            $table->string('institution_gender_code')->nullable();
            $table->integer('created_user_id')->nullable();
            $table->string('created')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
