<?php
namespace App\Dto;

class UserOutputDto
{
    public string $username;
    public string $email;

    public function __construct(string $username, string $email)
    {
        $this->username = $username;
        $this->email = $email;
    }
}