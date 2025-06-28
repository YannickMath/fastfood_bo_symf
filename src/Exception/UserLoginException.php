<?php
namespace App\Exception;

class UserLoginException extends \Exception
{
    public function __construct(string $identifier = '')
    {
        $message = "Login failed. Please check your credentials.";

        if (!empty($identifier)) {
            $message .= " (Tried: $identifier)";
        }

        parent::__construct($message);
    }
}