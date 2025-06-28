<?php
namespace App\Exception;

class UserAlreadyExistsException extends \Exception
{
    public function __construct(string $username)
    {
        parent::__construct("User with username '{$username}' already exists Please choose a different username.");
    }
}