<?php

namespace App\Service;

use App\Dto\UserRegisterDto;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

     public function createUser(UserRegisterDto $dto): Users
    {
        // Vérification unicité de l'email
        if (!empty($dto->email)) {
            $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $dto->email]);
            if ($existingUser) {
                throw new \RuntimeException('Email already exists');
            }
        }

        // Vérification unicité du nom d'utilisateur
        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['username' => $dto->username]);
        if ($existingUser) {
            throw new \RuntimeException('Username already exists');
        }

        $user = new Users();
        $user->setUsername($dto->username);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $dto->password));
        $user->setEmail($dto->email ?? '');
        $user->setImage($dto->image);
        $user->setDescription($dto->description);
        $user->setIsAdmin($dto->isAdmin ?? false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

public function deleteUser(Users $user): void
{
    $this->entityManager->remove($user);
    $this->entityManager->flush();
}

public function updateUser(Users $user, UserRegisterDto $dto): Users
{
    // Vérification unicité de l'email
    if (!empty($dto->email) && $dto->email !== $user->getEmail()) {
        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $dto->email]);
        if ($existingUser) {
            throw new \RuntimeException('Email already exists');
        }
    }

    // Vérification unicité du nom d'utilisateur
    if ($dto->username !== $user->getUsername()) {
        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['username' => $dto->username]);
        if ($existingUser) {
            throw new \RuntimeException('Username already exists');
        }
    }

    $user->setUsername($dto->username);
    if (!empty($dto->password)) {
        $user->setPassword($this->passwordEncoder->hashPassword($user, $dto->password));
    }
    $user->setEmail($dto->email ?? '');
    $user->setImage($dto->image);
    $user->setDescription($dto->description);
    $user->setIsAdmin($dto->isAdmin ?? false);

    $this->entityManager->flush();

    return $user;

}

public function findUserById(int $id): ?Users
{
    return $this->entityManager->getRepository(Users::class)->find($id);
}

}