<?php
namespace App\Exception;

class UserEmailAlreadyExistsException extends \Exception
{
    public function __construct(string $email)
    {
        parent::__construct("User with email '{$email}' already exists. Please choose a different email.");
    }
}