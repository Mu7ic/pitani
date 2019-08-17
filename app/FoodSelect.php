<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FoodSelect extends Model
{
    protected $fillable = [
        'date','user_id','zavtrak','obed', 'ujin'
    ];
}
