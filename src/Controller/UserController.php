<?php

namespace App\Controller;

use App\Dto\User\Input\UserRegisterInputDto;
use App\Dto\User\Input\UserUpdateInputDto;
use App\Dto\User\Output\UserOutputDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class UserController extends AbstractController
{   ## Route to get one user by ID
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

    ## Route to get all users
    #[Route('/api/user', methods: ['GET'])]
    public function getAllUsers(UserService $userService): JsonResponse
    {
        $users = $userService->getAllUsers();
        $dtos = [];

        foreach ($users as $user) {
            $dtos[] = new UserOutputDto($user->getUsername(), $user->getEmail() ?? '');
        }

        return $this->json($dtos);
    }

    #[Route('/api/user/register', methods: ['POST'])]
public function register(
    Request $request,
    ValidatorInterface $validator,
    UserService $userService
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $dto = new UserRegisterInputDto($data);

    $errors = $validator->validate($dto);
    if (count($errors) > 0) {
        $errorsArray = [];
        foreach ($errors as $error) {
            $errorsArray[$error->getPropertyPath()][] = $error->getMessage();
        }
        return new JsonResponse(['errors' => $errorsArray], JsonResponse::HTTP_BAD_REQUEST);
    }

    $user = $userService->createUser($dto);

    return new JsonResponse([
        'status' => 'User created',
        'id' => $user->getId(),
    ], JsonResponse::HTTP_CREATED);
}

    #[Route('/api/user/login', methods: ['POST'])]
public function login(
    Request $request,
    UserService $userService,
    JWTTokenManagerInterface $JWTManager
    ): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        return new JsonResponse(['error' => 'Username/password are required'], 400);
    }
    
    $user = $userService->loginUser($username, $password);

    $token = $JWTManager->create($user);

    return new JsonResponse(['token' => $token], 200);
}

    ## Route to delete a user by ID
    #[Route('/api/user/{id}', methods: ['DELETE'])]
    public function deleteUser(int $id, UserService $userService): JsonResponse
    {
        $user = $userService->findUserById($id);

        try {
            $userService->deleteUser($user);
            return new JsonResponse(['status' => 'User deleted'], 204);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Deletion failed'], 500);
        }
    }

   #[Route('/api/user/{id}/update', methods: ['PUT'])]
public function updateUser(
    int $id,
    Request $request,
    ValidatorInterface $validator,
    UserService $userService
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $dto = new UserUpdateInputDto($data); 

    $errors = $validator->validate($dto);
    if (count($errors) > 0) {
        $errorsArray = [];
        foreach ($errors as $error) {
            $errorsArray[$error->getPropertyPath()][] = $error->getMessage();
        }
        return new JsonResponse(['errors' => $errorsArray], 400);
    }

    try {
        $user = $userService->findUserById($id);
        $updatedUser = $userService->updateUser($user, $dto);

        return new JsonResponse([
            'status' => 'User updated',
            'id' => $updatedUser->getId(),
        ], 200);

    } catch (\App\Exception\UserNotFoundException $e) {
        return new JsonResponse(['error' => $e->getMessage()], 404);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Update failed'], 500);
    }

    }
}