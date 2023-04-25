<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionShifts extends Model
{
    use HasFactory;
    protected $fillable = ['shift_id','institution_id','shift_option_id','shift_option_name'];
}
