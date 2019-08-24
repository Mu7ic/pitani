<?php

namespace App\Http\Controllers;

use App\BalanceHistory;
use App\Datefood;
use App\FoodSelect;
use App\Help;
use App\Users;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    public function users(){
        return Users::where(['isAdmin'=>0,'isActive'=>1])->get();
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

    public function checkPass($password){
        $user=Users::where(['password'=>md5($password)])->first();
        if(!empty($user))
            $response=['error'=>true,'message'=>'Password has exist'];
        else
            $response = ['error'=>false,'Password not exist'];
        return response($response,202);
    }

    public function food_select(Request $request)
    {
        $foodselect = FoodSelect::where(['date' => $request->date])->get();
        if (!empty($foodselect)) {
            $zavtrak = 0;
            $obed = 0;
            $ujin = 0;
            foreach ($foodselect as $food) {
                if(!empty($food)){
                if ($food->zavtrak == 1)
                    $zavtrak++;
                if ($food->obed == 1)
                    $obed++;
                if ($food->ujin == 1)
                    $ujin++;
                }
            }
            !empty($foodselect) ? $price = $this->getDayPrice($request->date) : $price=0;
            !empty($foodselect) ? $summa = ($zavtrak * !empty($price->zavtrak) ? $price->zavtrak : 0) + ($obed * !empty($price->obed) ? $price->obed : 0 ) + ($ujin * !empty($price->ujin) ? $price->ujin : 0 ) : $summa=0;
            $response = ['count_zavtrak' => $zavtrak, 'count_obed' => $obed, 'count_ujin' => $ujin, 'summa' => $summa];
        } else $response = ['error' => true, 'message' => 'Date is empty in database'];

        return response($response, 202);
    }

    public function getText(){
        $text=Help::select(['text'])->first();
        $response=['error'=>false,'text'=>$text->text];
        return response($response,202);
    }

    public function updateText(Request $request){
        $text=Help::find(1);
        $text->text=$request->get('text');
        if($text->save())
            return response(['error'=>false,'message'=>'Text is successfuly updated'],202);
        else
            return response(['error'=>true,'There is an error occured!'],202);
    }


    public function getBalance($id,$start_date,$end_date){
        if(isset($id) && isset($start_date)){

            $v_ostatok=$this->getSummaWithDate($id,$start_date,$end_date);

            //eated_money
            $eated_money=$this->checkEatedMoney($id);

            //summa balanca
            $balanceCurrent=$this->getBalanceSumm($id);

            $balanceHistory=$this->getBalanceSumm($id)-$v_ostatok;


            if(!is_null($end_date)) {
                $database = FoodSelect::where(['user_id' => $id])->whereBetween('date', [$start_date, $end_date])->get();
                $summaAll = 0;
                $summa_z = 0;
                $summa_o = 0;
                $summa_u = 0;
                if(!empty($database)){
                foreach ($database as $date) {
                    $sena = $this->getDayPrice($date->date);
                    if ($date->zavtrak == 1) {
                        $summa_z += $sena->zavtrak;
                    }

                    if ($date->obed == 1) {
                        $summa_o += $sena->obed;
                    }

                    if ($date->ujin == 1) {
                        $summa_u += $sena->ujin;
                    }
                    $sena = ($date->zavtrak == 1 ? $sena->zavtrak : 0) + ($date->obed == 1 ? $sena->obed : 0) + ($date->ujin == 1 ? $sena->ujin : 0);
                    $summaAll = $summa_z + $summa_o + $summa_u;
                    //$v_ostatok=$v_ostatok-$summaAll;
                    $array[] = [
                        'date' => $date->date,
                        'balanceHistory' => $balanceCurrent - $summaAll - $v_ostatok + $sena,
                        //'currentBalance'=>$balanceCurrent-$summaAll,
                        //'summaBetween'=>$v_ostatok,
                        'summa' => round($sena, 2),
                        //x'balanceHistory'=>round($balanceHistory,2),
                        'ostatok' => round($balanceHistory - $summaAll, 2),
                    ];
                }
            }
                $response=['reports'=>!empty($array) ? $array : null,'date_start'=>$start_date,'end_date'=>$end_date,'v_ostatok'=>$balanceCurrent-$eated_money,];
            }
            return response($response,202);
        }else
            return response(['error'=>true],202);
    }

    private function getSummaWithDate($id,$start_date,$end_date){
        $database = FoodSelect::where(['user_id' => $id])->whereNotBetween('date', [$start_date, $end_date])->get();
        $summa=0;$count_obed=0;$count_zavtrak=0;$count_ujin=0;
        foreach ($database as $date){
            $sena=$this->getDayPrice($date->date);
            if($date->zavtrak==1){
                $summa+=!empty($sena->zavtrak) ? $sena->zavtrak : 0;
                $count_zavtrak++;
            }

            if($date->obed==1){
                $summa+=!empty($sena->obed) ? $sena->obed : 0;
                $count_obed++;
            }

            if($date->ujin==1){
                $count_ujin++;
                $summa+=!empty($sena->ujin) ? $sena->ujin : 0;
            }

        }
        return $summa;
    }

    public function checkEatedMoney($id){
        $database = FoodSelect::where(['user_id' => $id])->get();
        $summa=0;$count_obed=0;$count_zavtrak=0;$count_ujin=0;
        foreach ($database as $date){
            $sena=$this->getDayPrice($date->date);
            if($date->zavtrak==1){
                $summa+=!empty($sena->zavtrak) ? $sena->zavtrak : 0;
                $count_zavtrak++;
            }

            if($date->obed==1){
                $summa+=!empty($sena->obed) ? $sena->obed : 0;
                $count_obed++;
            }

            if($date->ujin==1){
                $count_ujin++;
                $summa+=!empty($sena->ujin) ? $sena->ujin : 0;
            }
        }
        return $summa;
    }

    //Общий баланс
    private function getBalanceSumm($id){
      return  BalanceHistory::where(['user_id'=>$id])->sum('money');
    }

    // Цена общий еды
    private function getDatePrice($date){
        return  Datefood::select(['zavtrak','obed','ujin'])->where(['date'=>$date])->first();

    }

    // Цена на день
    private function getDayPrice($date){
        return  Datefood::select(['zavtrak','obed','ujin'])->where(['date'=>$date])->first();

    }



}
