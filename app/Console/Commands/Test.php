<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use App\Users;
use App\FoodSelect;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавляет в базу данных даты выборки питание';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $date = date('Y-m-d', strtotime(date('') . ' +1 day'));
//        $select=Users::where('isAdmin',0)->where('isActive',1)->get();
//
//        foreach ($select as $user){
//            $food=FoodSelect::where(['user_id'=>$user->id,'date'=>$date])->first();
//            if(empty($food)){
//                $foodSelect= new FoodSelect();
//                $foodSelect->date=$date;
//                $foodSelect->user_id=$user->id;
//                $foodSelect->zavtrak=1;
//                $foodSelect->obed=1;
//                $foodSelect->ujin=0;
//                $foodSelect->created_at=date('Y-m-d H:i:s');
//                $foodSelect->updated_at=date('Y-m-d H:i:s');
//                $foodSelect->save();
//                $this->comment($user->id.PHP_EOL);
//            }
//        }


    }
}
