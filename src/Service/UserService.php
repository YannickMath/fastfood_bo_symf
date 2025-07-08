<?php

namespace App\Service;

use App\Dto\User\Input\UserRegisterInputDto;
use App\Dto\User\Input\UserUpdateInputDto;
use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Exception\UserEmailAlreadyExistsException;
use App\Exception\UserLoginException;
use App\Exception\UserNotFoundException;
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
        UsersRepository $usersRepository,
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

   public function loginUser(string $identifier, string $password): User
{
    $user = $this->usersRepository->findOneBy(['email' => $identifier]);

    if (!$user || !$this->passwordEncoder->isPasswordValid($user, $password)) {
        throw new UserLoginException();
    }

    return $user;
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
        if (!$id) {
            throw new UserNotFoundException();
        }
        return $this->usersRepository->findUserById($id);
    }

    ## Method to get all users
    public function getAllUsers(): array
    {
        return $this->usersRepository->findAll();
    }
    
    ## Method to update a user
        public function updateUser(User $user, UserUpdateInputDto $dto): User
    {
        if (!empty($dto->username)) {
            $user->setUsername($dto->username);
        }
        if (!empty($dto->email)) {
            $user->setEmail($dto->email);
        }
        if (!empty($dto->password)) {
            $user->setPassword($this->passwordEncoder->hashPassword($user, $dto->password));
        }

        $this->entityManager->flush();

        return $user;
    }

    // ## Method to check if a user is connected
    // public function connectedUser(): ?User
    // {
    //     $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    //     if (!$token) {
    //         return null;
    //     }

    //     $token = str_replace('Bearer ', '', $token);
    //     $user = $this->usersRepository->findOneBy(['apiToken' => $token]);

    //     if (!$user) {
    //         return null;
    //     }

    //     return $user;
    // }

    ## Method to check if a user has admin rights
    public function isGranted($user): bool
    {
    if (!$user instanceof User) {
        throw new UserNotFoundException();
    }

    return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}