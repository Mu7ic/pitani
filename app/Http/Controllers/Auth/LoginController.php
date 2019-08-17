<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Users;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Passport\Client;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
class LoginController extends Controller
{


    private $client;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->client=Client::find(1);
        $this->middleware('guest')->except('logout');
    }

    /*
    * password string
    */

    public function login (Request $request) {

        $request->validate([
            'password' => 'required|string',
        ]);

        $user = User::where('password', md5($request->password))->first();

        if ($user) {

            $token = $user->createToken('Laravel Password Grant Client')->accessToken;

            $response = ['token_type'=>'Bearer','access_token' => $token,'fname'=>$user->fname,'name'=>$user->name, 'isAdmin'=>$user->isAdmin,'lname'=>$user->lname,'idUser'=>$user->id,'balance'=>$user->balance,'isActive'=>$user->isActive];

            return response($response, 200);
        } else {
            $response = ['error'=>'true','message'=>'User does not exist'];
            return response($response, 422);
        }
        $response = ['token' => "ddasdfagasd"];
        return response($response, 200);
    }

    // Logout from system
    public function logout (Request $request) {

        $token = $request->user()->token();
        $token->revoke();

        $response = ['message'=>'You have been succesfully logged out!'];
        return response($response, 200);

    }

}
