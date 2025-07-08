<?php

namespace App\Service;

use App\Exception\ProductWithNoCategoryException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    public function getProductsByCategory(string $category): array
    {
        $products = $this->productRepository->findBy(['category' => $category]);

        if (empty($products)) {
            throw new ProductWithNoCategoryException($category);
        }

        return $products;
    }
}