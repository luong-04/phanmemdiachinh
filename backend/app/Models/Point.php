<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $table = 'points';

    // Cho phép Laravel ghi dữ liệu hàng loạt vào các cột này
    protected $fillable = [
        'user_id', 
        'parcel_id', 
        'point_name', 
        'x_coord',    
        'y_coord',    
        'z_coord',    
        'code'        
    ];
}