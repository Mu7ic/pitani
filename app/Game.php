<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['title', 'description', 'complexity', 'isActive'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    //
}
