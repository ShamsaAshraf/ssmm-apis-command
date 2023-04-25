<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionStaff extends Model
{
    use HasFactory;
    protected $fillable = ['staff_id','institution_id','staff_type_name','staff_status_id','staff_status_name','institution_position_id','institution_position_name'];
}
