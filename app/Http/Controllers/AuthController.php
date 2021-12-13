<?php

namespace App\Http\Controllers;

use App\Exceptions\AccessDeniedException;
use App\Exceptions\AttemptsExceededException;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;


class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function show(Request $request)
    {
        try {
            if (empty($request->cookie('auth_token'))){
                throw new UserNotFoundException();
            }
            $user = UserRepository::getByToken(trim($request->cookie('auth_token')));
        } catch (UserNotFoundException $e) {
            return view('auth.show');
        }


        return view('user.show', $user);
    }

    /**
     * @param Request $request
     * @return Application|Factory|Response|View
     */
    public function auth(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);

        try {
            $user = AuthService::handle($request);
        } catch (UserNotFoundException|AccessDeniedException $e){
            $errMsg = 'Неверные данные';

            return view('auth.show', compact('errMsg'));
        } catch (AttemptsExceededException $attemptsExceededException) {
            $errMsg = $attemptsExceededException->getMessage();

            return view('auth.show', compact('errMsg'));
        }

        return response()->view('user.show')->cookie('auth_token', $user->token, 3600);
    }

    public function logout(Request $request)
    {
        $user = UserRepository::getByToken(trim($request->cookie('auth_token')));
        if ($user) {
            UserRepository::deleteToken($user->token);
        }

        return redirect()->to('/');
    }
}
