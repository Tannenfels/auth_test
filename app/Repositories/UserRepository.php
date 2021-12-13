<?php

namespace App\Repositories;

use App\Exceptions\AttemptsExceededException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class UserRepository
{
    /**
     * @param string $token
     * @return User|null
     * @throws UserNotFoundException
     */
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
                break;
            }
        }
        throw new UserNotFoundException();
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

    /**
     * @param string $name
     * @return User|null
     * @throws UserNotFoundException
     */
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
        throw new UserNotFoundException();
    }

    public static function storeToken(User $user)
    {
        $token = Str::random(60);
        $data = $user->id . ';' . $token . ';' . PHP_EOL;
        file_put_contents(App::basePath().'/storage/users/tokens.txt', $data , FILE_APPEND | LOCK_EX);

        return $token;
    }

    public static function deleteToken(string $token)
    {
        $tokens = file(App::basePath().'/storage/users/tokens.txt');

        foreach ($tokens as $key => $value){
            $t = explode(';', $value);
            if($token == $t[1]){
                unset($tokens[$key]);
            }
        }
    }

    /**
     * @param int $id
     * @throws AttemptsExceededException
     */
    public static function checkAttempts(int $id)
    {
        $handle = file(App::basePath().'/storage/users/login_attempts.txt');
        $count = 0;

        foreach ($handle as $key => $value) {

            $attemptString = explode(';', $value);
            $userId = $attemptString[0];
            $date = Carbon::parse($attemptString[1]);
            $isInRange = Carbon::now()->lessThanOrEqualTo($date->addMinutes(5));

            if ($userId == $id && $isInRange) {
                $count++;
            }
            if ($count >= 3) {
                throw new AttemptsExceededException();
            }
        }
    }
    public static function setAttempt(int $id)
    {
        $data = $id . ';' . Carbon::now()->toDateTimeString() . ';' . PHP_EOL;
        file_put_contents(App::basePath().'/storage/users/login_attempts.txt', $data , FILE_APPEND | LOCK_EX);
    }
}
