<?php
/**
 * Created by PhpStorm.
 * User: Muzich
 * Date: 15.08.2019
 * Time: 14:20
 */

namespace App\Http\Controllers\Api;

use App\BalanceHistory;
use App\Datefood;
use App\FoodSelect;
use Carbon\Traits\Date;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Users;

class CreateController extends BaseController
{
    //Добавка пользователя
    public function add_user(Request $request)
    {

        if ($request->validate([
            'name' => 'required|string',
            'fname' => 'required|string',
            'lname' => '',
            'fio_parents' => 'required|string',
            'phone_parents' => 'required|integer',
            'password' => 'required|string',
        ]))
            $check_pass=Users::where(['password'=>md5($request->get('password'))])->first();
            if($check_pass==null) {
                $this->setUsers($request);
                $response = ['error' => false, 'message' => 'User successfuly added'];
            }else
                $response=['error'=>true,'message'=>'Please choose another password'];


            return response($response, 200);
    }

    public function update_users(Request $request)
    {

        if ($request->validate([
            'id'=>'required|integer',
            'name' => 'required|string',
            'fname' => 'required|string',
            'lname' => '',
            'fio_parents' => 'required|string',
            'phone_parents' => 'required|integer',
            //'password' => 'required|string',

        ]))

                $update=$this->updateUsers($request);
                if($update)
                $response = ['error' => false, 'message' => 'User successfuly updated'];
                else
                $response=['error'=>true,'message'=>'Please send the correct id'];


            return response($response, 200);
    }

    public function user_delete(Request $request){
        if ($request->validate([
            'id'=>'required|integer',
        ]))
            $del=$this->deleteUsers($request->get('id'));
          if($del)
              return response(['error'=>false,'message'=>'user is deactivated'],202);
          else
              return response(['error'=>true,'mesage'=>'Something went wrong, or user no exist'],202);
    }

    //Добавка баланса
    public function add_balance(Request $request)
    {

        if ($request->validate([
            'money' => 'required|string',
            'date' => 'required|date',
            'user_id' => 'required|integer',
        ]))
            $result = $this->setBalance($request);
        if($result)
        $response = ['error' => false, 'message' => 'Money has been added!'];
        else
            $response=['error'=>true,'message'=>'User does not exists'];
            return response($response, 200);
    }

    public function update_balance(Request $request)
    {

        if ($request->validate([
            'id_balance'=>'required|integer',
            'money' => 'required|integer',
            'date' => 'required|date',
            'user_id' => 'required|integer',
        ]))
            $result = $this->updateBalance($request);



        if($result)
        $response = ['error' => false, 'message' => 'Money has been updated!'];
        else
            $response=['error'=>true,'message'=>'Money does not updated!'];

        return response($response, 200);
    }
    public function delete_balance(Request $request)
    {

        if ($request->validate([
            'id_balance' => 'required|integer',
        ]))
            $result = $this->deleteBalance($request);
        if($result)
        $response = ['error' => false, 'message' => 'Money has been deleted!'];
        else
            $response=['error'=>true,'message'=>'Money does not exists'];
            return response($response, 200);
    }

    // Update Password user
    public function update_pass(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'id' => 'required|integer',
        ]);

        $new_password = $request->get('password');

        $user = Users::find($request->get('id'));
        $user->password = md5($new_password);
        if ($user->save())
            $response = ['message' => 'Password has been changed!'];
        return response($response, 200);

    }

    // Set Food in a Day
    public function food_create(Request $request)
    {
        if ($request->validate([
            'date' => 'required|date',
            'zavtrak' => 'required|integer',
            'obed' => 'required|integer',
            'ujin' => 'required|integer',
        ])){
            $result = $this->setFood($request);

        if ($result)
            $response = ['message' => 'Food has been created!'];
        else
            $response = ['error'=>true,'message'=>'This date already taken'];

        return response($response, 200);
        }
    }

    // Set Food in a Day
    public function food_update(Request $request, $id)
    {
        $datefood=Datefood::findOrFail($id);
        $datefood->fill($request->except(['id']));
        if($datefood->update())
        $response = ['message' => 'Food has been updated!','food'=>$datefood];
        return response($response, 200);

    }

    // Set Food in a Day
    public function food_day(Request $request)
    {
        $today=date('Y-m-d');
        if ($request->validate([
            'date' => 'required|date',
            'user_id'=>'required|integer',
            'zavtrak' => 'integer',
            'obed' => 'integer',
            'ujin' => 'integer',
        ]))
            $result = $this->setFoodDay($request);
        $date=$request->get('date');
        if($result){
        $response = ['message' => 'Food with date has been created!'];
        }else{
            $response=['error'=>true,'message'=>'Date is exist in base or you send past days'];
        }
        return response($response, 200);

    }

    public function food_day_update_user(Request $request)
    {
        $today=date('Y-m-d');
        if ($request->validate([
            'date' => 'required|date',
            'user_id'=>'required|integer',
            'zavtrak' => 'required|integer',
            'obed' => 'required|integer',
            'ujin' => 'required|integer',
        ]))
            $result = $this->setFoodDayUpdate($request);
        $date=$request->get('date');
        if($result){
        $response = ['message' => 'Food with date has been updated!'];
        }else{
            $response=['error'=>true,'message'=>'Date is exist in base or you send past days'];
        }
        return response($response, 200);

    }

    public function get_balance($id)
    {
        $balance=BalanceHistory::find($id);
        if(!empty($balance)){
            $response=['id'=>$balance->id,'money'=>$balance->money,'date'=>$balance->date,'user_id'=>$balance->user_id];
        }else{
            $response=['error'=>true,'message'=>'Balance does not exists'];
        }
        return response($response,200);
    }

    //Создаем пользователя
    private function setUsers($request)
    {
        $sub = new Users([
            'fname' => $request->get('fname'),
            'lname' => $request->get('lname'),
            'name' => $request->get('name'),
            'fio_parents' => $request->get('fio_parents'),
            'phone_parents' => $request->get('phone_parents'),
            'remember_token' => Str::random(32),
            'password' => md5($request->get('password')),
            'balance' => 0,
            'isAdmin' => 0,
            'isActive' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);
        if ($sub->save())
            return true;
        return false;
    }
    //Создаем пользователя
    private function updateUsers($request)
    {
        $sub=Users::find($request->get('id'));
        if(!$sub==null){
            $sub->name=$request->get('name');
            $sub->fname=$request->get('fname');
            $sub->lname=$request->get('lname');
            $sub->fio_parents=$request->get('fio_parents');
            $sub->phone_parents=$request->get('phone_parents');
            //$sub->password=md5($request->get('password'));

        }else
            return false;

        if ($sub->save())
            return true;
        return false;
    }

    // Создаем дневное питание цены
    private function setFood($request)
    {
        $date = $request->get('date');
        $zavtrak = $request->get('zavtrak');
        $obed = $request->get('obed');
        $ujin = $request->get('ujin');
        if(empty(Datefood::where(['date'=>$date])->first())){
        $food = new Datefood([
            'date' => $date,
            'zavtrak' => $zavtrak,
            'obed' => $obed,
            'ujin' => $ujin,
            'created_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        if ($food->save())
            return true;
        }
        return false;
    }

    public function food_day_update($date, Request $request)
    {

        if ($request->validate([
            'zavtrak' => 'required|integer',
            'obed' => 'required|integer',
            'ujin' => 'required|integer',
        ])) {
            $zavtrak = $request->get('zavtrak');
            $obed = $request->get('obed');
            $ujin = $request->get('ujin');
            $food = Datefood::where('date', $date)->first();
            $food->zavtrak = $zavtrak;
            $food->obed = $obed;
            $food->ujin = $ujin;
            $food->updated_at= date("Y-m-d H:i:s");
            if ($food->save())
                return response(['error' => false, 'message' => 'The date is updated'], 200);
        }
        return response(['error' => true, 'message' => 'Check the data please'], 200);
    }

   // Пополнения счета пользователя
    private function setBalance($request)
    {
        $date = $request->get('date');
        $money = $request->get('money');
        $user_id = $request->get('user_id');
        $user=Users::find($user_id);
        $food = new BalanceHistory([
            'date' => $date,
            'user_id' => $user_id,
            'money' => $money,
            'created_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);
        if(!empty($user)){
            if ($food->save()){
                return true;
            }
        }

        return false;
    }


    private function updateBalance($request)
    {
        $id_balance = $request->get('id_balance');
        $date = $request->get('date');
        $money = $request->get('money');
        $user_id = $request->get('user_id');
        $balance = BalanceHistory::find($id_balance);
        if (!empty($money) && !empty($date) && !empty($user_id)) {
            $balance->money = $money;
            $balance->date = $date;
            $balance->user_id = $user_id;
            $balance->updated_at = date("Y-m-d H:i:s");

            if (!empty($balance)) {
                if ($balance->save()) {
                    return true;
                }
            }
        }
        return false;
    }

    private function deleteBalance($request)
    {
        $id_balance = $request->get('id_balance');
        $do=BalanceHistory::find($id_balance)->delete();

        if($do)
            return true;
        return false;
    }

    private function setFoodDay($request)
    {
        $date_now = date('Y-m-d');

        $date = $request->get('date');
        $user_id = $request->get('user_id');
        $zavtrak = $request->get('zavtrak');
        $obed = $request->get('obed');
        $ujin = $request->get('ujin');
        if ($date>=$date_now) {
            if ($this->dateExists($date, $user_id)) {
                $food = new FoodSelect([
                    'date' => $date,
                    'user_id' => $user_id,
                    'zavtrak' => $zavtrak,
                    'obed' => $obed,
                    'ujin' => $ujin,
                    'created_at' => date("Y-m-d H:i:s"),
                    'update_at' => date("Y-m-d H:i:s"),
                ]);

                // Обновляем баланс
                if ($food->save())
                    return true;
            }
        }
        return false;
    }

    private function setFoodDayUpdate($request)
    {
        $date_now = date('Y-m-d');

        $date = $request->get('date');
        $user_id = $request->get('user_id');
        $zavtrak = $request->get('zavtrak');
        $obed = $request->get('obed');
        $ujin = $request->get('ujin');
        if ($date>=$date_now) {
            if ($this->dateExists($date, $user_id)==false) {
                $food = FoodSelect::where(['user_id'=>$user_id,'date'=>$date])->first();
                    $food->date = $date;
                $food->user_id = $user_id;
                $food->zavtrak = $zavtrak;
                $food->obed = $obed;
                $food->ujin = $ujin;
                $food->updated_at=date("Y-m-d H:i:s");


                // Обновляем баланс
                if ($food->save())
                    return true;
            }
        }
        return false;
    }

    private function dateExists($date,$id){
        $food=FoodSelect::where(['date'=>$date,'user_id'=>$id])->first();
        if(!empty($food))
            return false;
        return true;
    }

    private function deleteUsers($id){
        $user=Users::find($id);
        if($user->isActive==1)
            $user->isActive=0;
        if($user->save())
            return true;
        return false;
    }


    public function set_days($sdate,$edate){
//        $start_date='2020-09-01';
//        $end_date='2019-09-30';
//        $date=[];
//        for($i=0;$i<=272;$i++){
//          $date('Y-m-d', strtotime($start_date. ' + '.$i.' days'));
//            $now = strtotime($date);
//        }
//        //echo  date('Y-m-d', strtotime($start_date. ' + 2 days'));

        $now = strtotime($sdate);
        $end_date = strtotime($edate);

        $users=Users::where('isAdmin',0)->where('isActive',1)->get();


            while (date("Y-m-d", $now) != date("Y-m-d", $end_date)) {
                $day_index = date("w", $now);
                if ($day_index == 0 || $day_index == 6) {
                    //$date[]=date('Y-m-d',$now);
                } else {

                    //$this->check_days($user->id,date('Y-m-d', $now));
                    $date[]=date('Y-m-d', $now);
                }
                $now = strtotime(date("Y-m-d", $now) . "+1 day");
            }
        echo 'Добавлены дны для: <br>';
        $i=1;
        foreach ($users as $user) {

            foreach ($date as $d) {
                //$dating[]=['date'=>$d,'id'=>$user->id];
                $this->check_days($user->id,$d);
            }
            echo $i++.') '.$user->fname.' '.$user->name.' '.$user->lname.' </br>';
        }

        //var_dump($dating);
    }

    public function food_select($date)
    {
        $users = Users::where(['isAdmin' => 0,'isActive'=>1])->get();
        $price = $this->getDayPrice($date);
        if(!empty($users))
        $c_zavtrak=0;$c_obed=0;$c_ujin=0;
            foreach ($users as $user){
            $foodselect = FoodSelect::where(['date' => $date,'user_id'=>$user->id])->first();

                if(!empty($foodselect->zavtrak)){
                    if($foodselect->zavtrak==1)
                        $c_zavtrak++;
                }
                if(!empty($foodselect->obed)){
                    if($foodselect->obed==1)
                        $c_obed++;
                }
                if(!empty($foodselect->ujin)){
                    if($foodselect->ujin==1)
                        $c_ujin++;
                }
            $array[]=[
                'fio'=>$user->fname.' '.$user->name.' '.$user->lname,
                'zavtrak'=>!empty($foodselect->zavtrak) ? $foodselect->zavtrak : 0,
                'obed'=>!empty($foodselect->obed) ? $foodselect->obed : 0,
                'ujin'=>!empty($foodselect->ujin) ? $foodselect->ujin : 0,
                ];
        }
        if(!empty($price)){
                $price_z=$price->zavtrak;
                $price_o=$price->obed;
                $price_u=$price->ujin;
        }else {$price_u=0;$price_o=0;$price_z=0;}
        $response=['array'=>!empty($array) ? $array : 0,
            'count_zavtrak'=>$c_zavtrak,
            'count_obed'=>$c_obed,
            'count_ujin'=>$c_ujin,
            'sena_zavtrak'=>$price_z,
            'sena_obed'=>$price_o,
            'sena_ujin'=>$price_u,
            'summa_zavtrak'=>$summa_z=$c_zavtrak*$price_z,
            'summa_obed'=>$summa_o=$c_obed*$price_o,
            'summa_ujin'=>$summa_u=$c_ujin*$price_u,
            'summa'=>$summa_z+$summa_u+$summa_o,
        ];

        return response($response, 202);
    }

    private function check_days($id_user,$date){
        $food_day=FoodSelect::where(['user_id'=>$id_user,'date'=>$date])->first();
        if(empty($food_day)){
            $food=new FoodSelect();
            $food->date=$date;
            $food->user_id=$id_user;
            $food->zavtrak=1;
            $food->obed=1;
            $food->ujin=1;
            $food->created_at=date('Y-m-d H:i:s');
            $food->save();
        }
    }

    private function getDayPrice($date){
        return  Datefood::select(['zavtrak','obed','ujin'])->where(['date'=>$date])->first();
    }

}
