<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        App\Users::insert([
            'name'=>'Админ',
            'fname'=>' ',
            'lname'=>' ',
            'password'=>md5('12345678'),
            'phone_parents'=>' ',
            'fio_parents'=>' ',
            'balance'=>0,
            'isAdmin'=>1,
            'isActive'=>1,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
    }
}
