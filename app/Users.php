<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $fillable = [
        'name', 'fname', 'lname', 'password', 'isActive', 'isAdmin','fio_parents','phone_parents','password','balance','remember_token'
    ];
}
