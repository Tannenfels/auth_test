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
        $user = UserRepository::getByName(trim($request->name));

        $hash = Hash::check(trim($request->password), $user->hash);
        if ($hash != $user->hash){
            UserRepository::setAttempt($user->id);
            UserRepository::checkAttempts($user->id);
            throw new AccessDeniedException();
        } else {
            $user->token = UserRepository::storeToken($user);
        }

        return $user;
    }
}
