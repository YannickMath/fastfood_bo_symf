<?php

namespace App\Dto\User\Input;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateInputDto
{
    #[Assert\NotBlank]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*\d).+$/',
        message: 'Le mot de passe doit contenir au moins une majuscule et un chiffre.'
    )]
    public string $password;

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    public string $email;

    public function __construct(array $data)
    {
        $this->username = $data['username'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->email = $data['email'] ?? null;

    }
}