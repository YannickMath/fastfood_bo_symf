<?php
namespace App\Exception;
class UserLoginException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Invalid credentials.");
    }
}