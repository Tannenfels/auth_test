<?php


namespace App\Services;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public static function handle(Request $request): bool
    {
        $user = UserRepository::getByName(trim($request->name));

        $hash = Hash::make(trim($request->password));
        if (!empty($user) && $hash != $user->hash){
            unset($user);
        }
        return !empty($user);
    }
}
