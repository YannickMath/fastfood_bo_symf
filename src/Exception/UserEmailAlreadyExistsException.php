<?php
namespace App\Exception;

class UserEmailAlreadyExistsException extends \Exception
{
    public function __construct(string $email)
    {
        parent::__construct("Invalid email or choose a different email.");
    }
}