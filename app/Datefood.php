<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Datefood extends Model
{
    protected $fillable = [
        'date', 'zavtrak','obed', 'ujin'
    ];
}
