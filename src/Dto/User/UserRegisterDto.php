<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegisterDto
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
    public ?string $email = null;

    #[Assert\Length(max: 255)]
    public ?string $image = null;

    #[Assert\Length(max: 500)]
    public ?string $description = null;

    #[Assert\Type('bool')]
    public bool $isAdmin = false;

    public function __construct(array $data)
    {
        $this->username = $data['username'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->image = $data['image'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->isAdmin = $data['isAdmin'] ?? false;
    }
}