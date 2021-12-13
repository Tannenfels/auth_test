<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function show(Request $request)
    {
        if (empty($request->cookie('auth_token'))){
            return view('auth.show');
        }
        $user = UserRepository::getByToken(trim($request->cookie('auth_token')));

        return view('user.show', $user);
    }

    public function auth(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);
        $isAuthorised = AuthService::handle($request);
        $errMsg = 'Неверные данные';

        return $isAuthorised ? view('user.show') : view('auth.show', compact('errMsg'));
    }
}
