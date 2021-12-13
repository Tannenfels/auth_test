<?php

namespace App\Models;

use Exception;

class User
{
    public int $id;
    public string $name;
    public string $hash;

    public function __construct(string $name, string $hash)
    {
        $this->name = $name;
        $this->hash = $hash;
    }

    /**
     * @param int $id
     * @throws Exception
     */
    public function setId(int $id)
    {
        if (empty($this->id))
        {
            $this->id = $id;
        } else {
            throw new Exception('Unauthorised change of model ID occurred.');
        }
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $hash
     * @return User
     * @throws Exception
     */
    public static function createFromFileOutput(int $id, string $name, string $hash): User
    {
        $user = new User($name, $hash);
        $user->setId($id);

        return $user;
    }

    public function save()
    {

    }
}
