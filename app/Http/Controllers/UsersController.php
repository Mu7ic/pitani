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
        return Users::select(['id','name','fname','lname','fio_parents','phone_parents'])->where(['isAdmin'=>0,'isActive'=>1])->get();
    }
    //Список пополнения
    public function balance_list(){
        $balance=BalanceHistory::orderBy('date', 'DESC')->get();
        foreach ($balance as $bal) {
            $user=Users::find($bal->user_id);
            $response[]=['id'=>$bal->id,'fio'=>$user->fname.' '.$user->name.' '.$user->lname,'money'=>$bal->money,'date'=>$bal->date];
        }
        return response($response,200);
    }
    //Одиночная выборка пользователя
    public function getUser($id){
        $user=Users::find($id);
        if($user == null)
            $response = ['error'=>true,'message'=>'User does not exist'];
        else
            $response = ['idUser'=>$user->id,
                'fname'=>$user->fname,
                'name'=>$user->name,
                'lname'=>$user->lname,
                'fio_parents'=>$user->fio_parents,
                'phone_parents'=>$user->phone_parents,
            ];
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

    public function food_select_user($user_id,$date)
    {
        $foodselect = FoodSelect::select(['id','user_id','date','zavtrak','obed','ujin'])->where(['user_id' => $user_id,'date'=>$date])->first();

        if(!empty($foodselect)){

            $response=['error'=>false,'data'=>$foodselect];
        }else
            $response = ['error' => true, 'message' => 'Date is empty in database'];

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


    public function getBalance($id, $start_date, $end_datee)
    {
        $date = date('Y-m-d');
        if ($end_datee >= $date) {
            $end_date = $date;
        } else
            $end_date = $end_datee;

        $s_date='2019-08-01';

        if($start_date<=$s_date)
            $start_date=$s_date;

        if (isset($id) && isset($start_date)) {

            $v_ostatok = $this->getSummaWithDate($id, $start_date, $end_date);


            $balanceList = $this->getBalanceList($id, $start_date, $end_date);

            $eted_for_start_day=$this->checkEatedMoneyWithDate($id,$start_date);

            $eted_for_end_day=$this->checkEatedMoneyWithDate($id,$end_date);

            $ostatok_vhod=$oplata=$this->getBalanceSummForDay($id,$start_date);
            $ostatok_ishod=$oplata=$this->getBalanceSummForDay($id,$end_date);

            //eated_money
            $eated_money = $this->checkEatedMoney($id);

            //summa balanca
            $balanceCurrent = $this->getBalanceSumm($id);

            $balanceHistory = $this->getBalanceSumm($id) - $v_ostatok;
            $summa = 0;
            $summaAll = 0;
            $summa_z = 0;
            $summa_o = 0;
            $summa_u = 0;
            $i=0;

            if ($balanceList['count']>0) {
                foreach ($balanceList['check'] as $bal) {
                    $i++;

                    if ($balanceList['count']>1) {

                        if ($balanceList['count'] == $i) {
                            $start_dat = $this->addPlusDay($balanceList['check'][$balanceList['count'] - 1]->date);
                            //$start_dat = $balanceList['check'][count($balanceList)-1]->date;
                            $en_date = $end_date;
                        } else {
                            if($i==1)
                                $start_dat=$start_date;
                            else
                            $start_dat = $this->addPlusDay($balanceList['check'][$i - 1]->date);
                            //$start_dat = $balanceList['check'][$i-1]->date;
                            $en_date = $balanceList['check'][$i]->date;
                        }

                    }else{
                        $start_dat=$start_date;
                        $en_date=$end_date;
                    }

                    $oplata=$this->getBalanceSummForDay($id,$bal->date);

                    $eted_money_for_the_date=$this->checkEatedMoneyWithDate($id,date('Y-m-d',strtotime($bal->date.' - 1 day')));
                    //$summEated=$oplata-$eted_money_for_the_date;

                    $database = FoodSelect::where(['user_id' => $id])->whereBetween('date', [$start_dat, $en_date])->orderBy('date', 'DESC')->get();
                    $bl[] = [
                        'sort'=>date('Y-m-d',strtotime($bal->date.' - 1 day')),
                        'date' => $this->formatDay($bal->date)/*.' balance op=>'.$oplata.', et=>'.$eted_money_for_the_date*/,
                        'money'=>$bal->money,
                        'summa_rashod'=>'',
                        //'money' => $bal->money,
                        //'i' => $i,
                        //'s_date_en_date' => $start_dat.'=>'.$en_date,
                        //'e_date' => ,
                        'v_ostatok' => round($oplata-$eted_money_for_the_date,2)
                    ];


                    foreach ($database as $date) {

                        $oplata_date=$this->getBalanceSummForDay($id,$date->date);

                        $eted_money_for_the_date=$this->checkEatedMoneyWithDate($id,$date->date);

                        $sena = $this->getDayPrice($date->date);

                        if (!empty($sena)) {
                            if ($date->zavtrak == 1) {
                                $summa_z += !empty($sena->zavtrak) ? $sena->zavtrak : 0;
                            }

                            if ($date->obed == 1) {
                                $summa_o += !empty($sena->obed) ? $sena->obed : 0;
                            }

                            if ($date->ujin == 1) {
                                $summa_u += !empty($sena->ujin) ? $sena->ujin : 0;
                            }
                            $sena = ($date->zavtrak == 1 ? $sena->zavtrak : 0) + ($date->obed == 1 ? $sena->obed : 0) + ($date->ujin == 1 ? $sena->ujin : 0);
                            $summaAll = $summa_z + $summa_o + $summa_u;


                            $bl[] = [
                                'sort'=>$date->date,
                                'date' => $this->formatDay($date->date) /*. ' op=>'.$oplata_date.', et=>'.$eted_money_for_the_date*/,
                                //'balanceHistory' => $balanceCurrent - $summaAll + $sena,
                                'money' => '',
                                //'currentBalance'=>$balanceCurrent-$summaAll,
                                //'summaBetween'=>$v_ostatok,
                                //'ostatok'=>$oplata,
                                //'sel'=>$eted_money_for_the_date,

                                'summa_rashod' => round($sena, 2),
                                //x'balanceHistory'=>round($balanceHistory,2),
                                //'v_ostatok' => $bal->date >= $date->date ? round($balanceHistory + $summaAll - $sena - $bal->money, 2) : round($balanceHistory + $summaAll - $sena - $bal->money, 2),
                                'v_ostatok' => round($oplata_date-$eted_money_for_the_date, 2),
                            ];

                        }
                    }
                }
                //$bl=array_merge($blb,$bld);
                if (!empty($bl))
                   sort($bl);

            }else{
               // if (!is_null($end_date)) {
                    $database = FoodSelect::where(['user_id' => $id])->whereBetween('date', [$start_date, $end_date])->orderBy('date', 'ASC')->get();
                    $summaAll = 0;
                    $summa_z = 0;
                    $summa_o = 0;
                    $summa_u = 0;
                    if (!empty($database)) {
                        foreach ($database as $date) {
                            $sena = $this->getDayPrice($date->date);
                            if (!empty($sena)) {
                                if ($date->zavtrak == 1) {
                                    $summa_z += !empty($sena->zavtrak) ? $sena->zavtrak : 0;
                                }

                                if ($date->obed == 1) {
                                    $summa_o += !empty($sena->obed) ? $sena->obed : 0;
                                }

                                if ($date->ujin == 1) {
                                    $summa_u += !empty($sena->ujin) ? $sena->ujin : 0;
                                }
                                $sena = ($date->zavtrak == 1 ? $sena->zavtrak : 0) + ($date->obed == 1 ? $sena->obed : 0) + ($date->ujin == 1 ? $sena->ujin : 0);
                                $summaAll = $summa_z + $summa_o + $summa_u;


                                $bl[] = [
                                    'date' => $this->formatDay($date->date),
                                    //'balanceHistory' => $balanceCurrent - $summaAll + $sena,
                                    'money' => '',
                                    //'currentBalance'=>$balanceCurrent-$summaAll,
                                    //'summaBetween'=>$v_ostatok,

                                    'summa_rashod' => round($sena, 2),
                                    //x'balanceHistory'=>round($balanceHistory,2),
                                    'v_ostatok' => round($balanceCurrent - $summaAll, 2),
                                ];

                            }
                        }
                        //$array = array_merge($array, $bl);
                        //sort($bl);
                    }
            }
                $response = ['reports' => !empty($bl) ? $bl : null,/*'balanceList'=>$balanceList,*/
                    'date_start' => $this->formatDay($start_date), 'ishodyawiy_ostatok' => $ostatok_vhod-$eted_for_start_day, 'end_date' => $this->formatDay($end_date), 'v_ostatok' => $ostatok_ishod-$eted_for_end_day,];



            return response($response, 200);
        } else
            return response(['error' => true], 200);


    }

    private function addPlusDay($date){
        $d=explode('-',$date);
        $y=$d[0];$m=$d[1];$day=$d[2];
        $day=$day+1;
        return $y.'-'.$m.'-'.$day;

    }

    private function formatDay($date){
        $d=explode('-',$date);
        $y=$d[0];$m=$d[1];$day=$d[2];
        return $day.'/'.$m.'/'.$y;

    }

    private function getSummaWithDate($id,$start_date,$end_date){
        $database = FoodSelect::where(['user_id' => $id])->whereBetween('date', [$start_date, $end_date])->get();
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

    public function balance($id){
        return $this->getBalanceSumm($id) - $this->checkEatedMoney($id);
    }

    public function checkEatedMoney($id){
        $database = FoodSelect::where(['user_id' => $id])->where('date','<=',date('Y-m-d'))->get();
        $summa=0;
        foreach ($database as $date){
            $sena=$this->getDayPrice($date->date);
            if(!empty($sena)){
            if($date->zavtrak==1)
                $summa+=!empty($sena->zavtrak) ? $sena->zavtrak : 0;


            if($date->obed==1)
                $summa+=!empty($sena->obed) ? $sena->obed : 0;


            if($date->ujin==1)
                $summa+=!empty($sena->ujin) ? $sena->ujin : 0;

            }
        }
        return $summa;
    }

    public function checkEatedMoneyWithDate($id,$date_select){
        $database = FoodSelect::where(['user_id' => $id])->where('date','<=',$date_select)->get();
        $summa=0;
        foreach ($database as $date){
            $sena=$this->getDayPrice($date->date);
            if(!empty($sena)){
            if($date->zavtrak==1)
                $summa+=!empty($sena->zavtrak) ? $sena->zavtrak : 0;


            if($date->obed==1)
                $summa+=!empty($sena->obed) ? $sena->obed : 0;


            if($date->ujin==1)
                $summa+=!empty($sena->ujin) ? $sena->ujin : 0;

            }
        }
        return $summa;
    }

    //Общий баланс
    private function getBalanceSumm($id){
      return  BalanceHistory::where(['user_id'=>$id])->sum('money');
    }
    //Общий баланс
    private function getBalanceSummWithDate($id,$s_date){
        //$s_date='2019-09-02';
      return  BalanceHistory::where(['user_id'=>$id])->where('date','<=',$s_date)->sum('money');
    }

    private function getBalanceSummForDay($id,$s_day){
      return  $this->getBalanceSummWithDate($id,$s_day);
    }

    //Общий баланс
    private function getBalanceList($id,$date_s,$date_e){
      $check=BalanceHistory::select(['date','money'])->where(['user_id'=>$id])->whereBetween('date',[$date_s,$date_e])->orderBy('date','ASC')->get();
      if($check)
          return $array=['check'=>$check,'count'=>count($check)];
      return false;
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
