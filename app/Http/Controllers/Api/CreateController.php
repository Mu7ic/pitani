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
            'password' => 'required|string',

        ]))

                $update=$this->updateUsers($request);
                if($update)
                $response = ['error' => false, 'message' => 'User successfuly updated'];
                else
                $response=['error'=>true,'message'=>'Please send the correct id'];


            return response($response, 200);
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
        ]))
            $result = $this->setFood($request);

        if ($result)
            $response = ['message' => 'Food has been created!'];
        return response($response, 200);

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
        if ($request->validate([
            'date' => 'required|date',
            'user_id'=>'required|integer',
            'zavtrak' => 'integer',
            'obed' => 'integer',
            'ujin' => 'integer',
        ]))
            $result = $this->setFoodDay($request);


        $response = ['message' => 'Food with date has been created!'];
        return response($response, 200);

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
            $sub->password=md5($request->get('password'));

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
        return false;
    }
   // Пополнения счета пользователя
    private function setBalance($request)
    {
        $date = $request->get('date');
        $money = $request->get('money');
        $user_id = $request->get('user_id');
        // Добавляем в историю
        $food = new BalanceHistory([
            'date' => $date,
            'user_id' => $user_id,
            'money' => $money,
            'created_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);
        // Обновляем баланс
        if ($food->save()){
            $user=Users::findOrFail($user_id);
            $user->balance=$user->balance+$money;
            if($user->update())
            return true;
        }
        return false;
    }

    private function setFoodDay($request)
    {
        $date = $request->get('date');
        $user_id = $request->get('user_id');
        $zavtrak = $request->get('zavtrak');
        $obed = $request->get('obed');
        $ujin = $request->get('ujin');
        // Добавляем в историю
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
        return false;
    }

}
