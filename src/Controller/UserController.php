<?php

namespace App\Controller;

use App\Dto\User\Input\UserRegisterInputDto;
use App\Dto\User\Output\UserOutputDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    ## Route to register a new user
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
            return new JsonResponse(['errors' => $errorsArray], 400);
        }

        try {
            $user = $userService->createUser($dto);
            return new JsonResponse(['status' => 'User created', 'id' => $user->getId()], 201);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Registration failed'], 500);
        }
    }

    ## Route to delete a user by ID
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