<?php


namespace App\Services;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\AttemptsExceededException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @param Request $request
     * @return User
     * @throws UserNotFoundException|AccessDeniedException
     * @throws AttemptsExceededException
     */
    public static function handle(Request $request)
    {
        try {
            $user = UserRepository::getByName(trim($request->name));
        } catch (UserNotFoundException $exception) {
            UserRepository::setAttempt($request->getClientIp());
            UserRepository::checkAttempts($request->getClientIp());

            throw $exception;
        }


        $hash = Hash::check(trim($request->password), $user->hash);
        if ($hash != $user->hash){
            UserRepository::setAttempt($request->getClientIp(), $user->id);
            UserRepository::checkAttempts($request->getClientIp(), $user->id);
            throw new AccessDeniedException();
        } else {
            $user->token = UserRepository::storeToken($user);
        }

        return $user;
    }
}
