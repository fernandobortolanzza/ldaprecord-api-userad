<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\UserResource;
use App\Models\User;
use LdapRecord\Models\ActiveDirectory\User as UserAD;
use LdapRecord\Models\Attributes\AccountControl;
use Illuminate\Http\Request;
use LdapRecord\Container;

class UserController extends Controller
{

    public function details (User $user) 
    {
        return response(new UserResource($user));
    }

    public function validastatus (User $user)
    {
        return response()->json([
                'data' => [
                    'status' => $this->fnValidaStatus($user->email)
                ]
        ]);
    }


    public function validasenha (Request $request)
    {
        if (!$this->fnValidaSenha($request->email, $request->password))
        {
            return response()->json([
                'data' => [
                    'msg' => 'Senha invalida'
                ]
            ]);
        } else {
            return response()->json([
                'data' => [
                    'msg' => 'Senha ok'
                ]
            ]);
        }

    }



    /******** FUNÇÕES ********/
    public function fnValidaStatus($email)
    {

        #$connection = Container::getConnection('default');
        $userbyMail = UserAD::findByOrFail('mail', $email);


        $uac = new AccountControl(
            $userbyMail->getFirstAttribute('userAccountControl')
        );

        if ($uac->has(AccountControl::ACCOUNTDISABLE)) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function fnValidaSenha($email, $password) {
        
        $connection = Container::getConnection('default');
        $userbyMail = UserAD::findByOrFail('mail', $email);

        if ($connection->auth()->attempt($userbyMail->getDn(), $password)) {
            return true;
        } else {
            return false;
        }

    }
}
