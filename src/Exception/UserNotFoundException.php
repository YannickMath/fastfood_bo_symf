<?php
namespace App\Exception;

class UserNotFoundException extends \Exception
{
    public function __construct(string $username)
    {
        parent::__construct("User with username '{$username}' not found.");
    }
}