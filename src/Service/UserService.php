<?php

namespace App\Service;

use App\Dto\User\Input\UserRegisterInputDto;
use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Exception\UserEmailAlreadyExistsException;
use App\Exception\UserLoginException;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
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
                throw new UserEmailAlreadyExistsException($dto->email);
            }
        }

        $existingUser = $this->usersRepository->findOneBy(['username' => $dto->username]);
        if ($existingUser) {
            throw new UserAlreadyExistsException($dto->username);
        }

        $user = new User();
        $user->setUsername($dto->username);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $dto->password));
        $user->setEmail($dto->email ?? '');
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    ## Method to login a user
    public function loginUser(string $username, string $password): ?User
    {
        $user = $this->usersRepository->findOneBy(['username' => $username]);
        if (!$user) {
            throw new UserLoginException($username);
        }

        if ($this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new UserLoginException($user->getEmail());
        }

        return null;
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