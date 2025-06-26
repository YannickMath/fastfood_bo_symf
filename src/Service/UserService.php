<?php

namespace App\Service;

use App\Dto\User\Input\UserRegisterInputDto;
use App\Entity\User;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordEncoder;
    private UsersRepository $usersRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        UsersRepository $usersRepository
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->usersRepository = $usersRepository;
    }

    ## Method to create a new user
    public function createUser(UserRegisterInputDto $dto): User
    {
        if (!empty($dto->email)) {
            $existingUser = $this->usersRepository->findOneBy(['email' => $dto->email]);
            if ($existingUser) {
                throw new \RuntimeException('Email already exists');
            }
        }

        $existingUser = $this->usersRepository->findOneBy(['username' => $dto->username]);
        if ($existingUser) {
            throw new \RuntimeException('Username already exists');
        }

        $user = new User();
        $user->setUsername($dto->username);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $dto->password));
        $user->setEmail($dto->email ?? '');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    ## Method to login a user
    public function loginUser(string $username, string $password): ?User
    {
        $user = $this->usersRepository->findOneBy(['username' => $username]);
        if (!$user) {
            return null; // User not found
        }

        if ($this->passwordEncoder->isPasswordValid($user, $password)) {
            return $user; // Password is valid
        }

        return null; // Invalid password
    }

    ## Method to delete a user
    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
    
    ## Method to find a user by ID
    public function findUserById(int $id): ?User
    {
        return $this->usersRepository->findUserById($id);
    }

    ## Method to update a user
    public function getAllUsers(): array
    {
        return $this->usersRepository->findAll();
    }
}