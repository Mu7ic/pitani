<?php

namespace App\Http\Controllers;

use App\BalanceHistory;
use App\Datefood;
use App\FoodSelect;
use App\Users;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    public function users(){
        return Users::where(['isAdmin'=>0])->get();
    }
    //Список пополнения
    public function balance_list(){
        return BalanceHistory::all();
    }
    //Одиночная выборка пользователя
    public function getUser($id){
        $user=Users::find($id);
        if($user == null)
            $response = ['error'=>true,'message'=>'User does not exist'];
        else
            $response = ['idUser'=>$user->id,'fname'=>$user->fname,'name'=>$user->name,'lname'=>$user->lname,'balance'=>$user->balance,'isActive'=>$user->isActive];
        return response($response,202);
    }

    public function food_select(Request $request){
        $foodselect=FoodSelect::where(['date'=>$request->date])->get();
        $zavtrak=0;$obed=0;$ujin=0;
        foreach ($foodselect as $food){
            if($food->zavtrak==1)
                $zavtrak++;
            if($food->obed==1)
                $obed++;
            if($food->ujin==1)
                $ujin++;
        }
        $price=$this->getDayPrice($request->date);
        $summa=($zavtrak*$price->zavtrak) + ($obed * $price->obed) +($ujin * $price->ujin);
        $response=['count_zavtrak'=>$zavtrak,'count_obed'=>$obed,'count_ujin'=>$ujin,'summa'=>$summa];

        return response($response,202);
    }
    // Цена на день
    private function getDayPrice($date){
        return  Datefood::select(['zavtrak','obed','ujin'])->where(['date'=>$date])->first();

    }



}
