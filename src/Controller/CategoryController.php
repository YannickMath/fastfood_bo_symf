<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    // Route to get all categories
    #[Route('/api/categories', methods: ['GET'])]
    public function getAllCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        $dtos = [];

        foreach ($categories as $category) {
            $dtos[] = [
                'id' => $category->getId(),
                'type' => $category->getType(),
            ];
        }

        return new JsonResponse($dtos);
    }

}