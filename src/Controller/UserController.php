<?php

namespace App\Controller;

use App\Dto\UserOutputDto;
use App\Dto\UserRegisterDto;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
{
// Get one user by ID
// GET /api/user/{id}
#[Route('/api/user/{id}', methods: ['GET'])]
public function showUser(int $id, UserService $userService): JsonResponse
{
    $user = $userService->findUserById($id);

    if (!$user) {
        return new JsonResponse(['error' => 'User not found'], 404);
    }

    $dto = new UserOutputDto($user->getUsername(), $user->getEmail() ?? '');
    return $this->json($dto);
}


// Register a new user
// POST /api/user/register
#[Route('/api/user/register', methods: ['POST'])]
public function register(
    Request $request,
    ValidatorInterface $validator,
    UserService $userService
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    $dto = new UserRegisterDto($data);
    $errors = $validator->validate($dto);

    if (count($errors) > 0) {
        return new JsonResponse(['errors' => (string) $errors], 400);
    }

    try {
        $user = $userService->createUser($dto);

        return new JsonResponse(['status' => 'User created', 'id' => $user->getId()], 201);
    } catch (\RuntimeException $e) {
        return new JsonResponse(['error' => $e->getMessage()], 400); // ex : email déjà utilisé
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Registration failed'], 500);
    }
}

// Delete a user
// DELETE /api/user/{id}
#[Route('/api/user/{id}', methods: ['DELETE'])]
public function deleteUser(int $id, UserService $userService): JsonResponse
{
    $user = $userService->findUserById($id);
    if (!$user) {
        return new JsonResponse(['error' => 'User not found'], 404);    

}

    try {
        $userService->deleteUser($user);
        return new JsonResponse(['status' => 'User deleted'], 204);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Deletion failed'], 500);
    }
}


}