<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;
    protected $fillable = ['institutionId','shiftId','institution_name','alternative_name','institution_code','address','postal_code','date_opened','year_opened','longitude','latitude','area_id','area_name',
        'area_administrative_id','area_administrative_name','institution_locality_name','institution_locality_international_code','institution_locality_national_code','institution_classification_id','institution_classification_name','institution_type_id','institution_type_name','institution_type_national_code','institution_ownership_id',
        'institution_ownership_international_code','institution_ownership_national_code','institution_status_id','institution_status_name','institution_status_code','institution_sector_id','institution_sector_name','institution_sector_international_code','institution_sector_national_code','institution_provider_id','institution_provider_name','institution_provider_international_code','institution_provider_national_code',
        'institution_gender_id','institution_gender_name','institution_gender_code','created_user_id','created','status'];
}
