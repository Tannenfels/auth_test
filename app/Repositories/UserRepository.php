<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class UserRepository
{
    public static function getByToken(string $token): ?User
    {
        $tokens = file(App::basePath().'/storage/users/tokens.txt');

        foreach ($tokens as $value){
            $t = explode(';', $value);
            if($token == $t[1]){
                $userIdByToken = $t[0];
                try{
                    $handle = @fopen(App::basePath().'/storage/users/users.txt', 'r');
                    if ($handle) {
                        while (($buffer = fgets($handle, 4096)) !== false) {
                            $userId = explode(';', $buffer)[0];
                            if ($userId == $userIdByToken){
                                $userName = explode(';', $buffer)[1];
                                $hash = explode(';', $buffer)[2];
                                return User::createFromFileOutput($userId, $userName, $hash);
                            }
                        }
                    }
                } finally {
                    fclose($handle);
                }

            }
        }
        return null;
    }

    public static function getLastId():int
    {
        $userId = 0;

        try{
            $handle = @fopen(App::basePath().'/storage/users/users.txt', 'r');
            if ($handle) {
                fseek($handle, -1, SEEK_END);
                $buffer = fgets($handle, 4096);
                $userId = explode(';', $buffer)[0];
            }
        } finally {
            fclose($handle);
        }

        return $userId;
    }

    public static function getByName(string $name): ?User
    {
        try{
            $handle = @fopen(App::basePath().'/storage/users/users.txt', 'r');
            if ($handle) {
                while (($buffer = fgets($handle, 4096)) !== false) {
                    $userName = explode(';', $buffer)[1];
                    if ($userName == $name){
                        $userId = explode(';', $buffer)[0];
                        $hash = explode(';', $buffer)[2];
                        return User::createFromFileOutput($userId, $userName, $hash);
                    }
                }
            }
        } finally {
            fclose($handle);
        }
        return null;
    }

    public static function storeToken(User $user)
    {
        $token = Str::random(60);
        $data = $user->id . ';' . $token . ';' . PHP_EOL;
        file_put_contents('logs.txt', $data , FILE_APPEND | LOCK_EX);

        return $token;
    }
}
