<?php

namespace App\Service;

use App\Dto\Product\Input\ProductInputDto;
use App\Entity\Product;
use App\Exception\ProductCannotBeUpdatedException;
use App\Exception\ProductNotDeletedException;
use App\Exception\ProductNotFoundException;
use App\Exception\ProductNotPossibleToCreateException;
use App\Exception\ProductWithNoCategoryException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    ## Method to get all products
    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

    ## Method to find a product by ID
    public function showProduct(int $id): ?Product
    {
        return $this->productRepository->find($id);
    } 

    ## Method to find a product by name
    public function findProductByName(string $name): Product
    {
        $product = $this->productRepository->findOneBy(['name' => $name]);
        dump($product);
        if (!$product) {
            throw new ProductNotFoundException($name);
        }

        return $product;
    }
    
    ## Method to create a new product
    public function createProduct(ProductInputDto $dto): Product
    {
    $existingProduct = $this->productRepository->findOneBy(['name' => $dto->name]);

    if ($existingProduct) {
        throw new ProductNotPossibleToCreateException($dto->name);
    }

    $product = new Product();
    $product->setName($dto->name);
    $product->setPrice($dto->price);
    $product->setDescription($dto->description ?? '');
    $product->setCategory($dto->category ?? '');
    $product->setCreatedAt(new \DateTimeImmutable());
    $product->setUpdatedAt(new \DateTime());

    $this->entityManager->persist($product);
    $this->entityManager->flush();

    return $product;
}

    ## Method to update a product
    public function updateProduct(int $id, ProductInputDto $dto): Product
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw new ProductNotFoundException($dto->name);
        }

        $product->setName($dto->name);
        $product->setPrice($dto->price);
        $product->setDescription($dto->description ?? '');
        $product->setCategory($dto->category ?? '');
        $product->setUpdatedAt(new \DateTime());
      
        if (empty($dto->name) || empty($dto->price) || empty($dto->category) || empty($dto->description)) {
            throw new ProductCannotBeUpdatedException($dto->name);
        }
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    ## Method to delete a product
    public function deleteProduct(int $id): void
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw new ProductNotDeletedException("Product with ID $id not found.");
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    ## Method to get products by category
    public function getProductsByCategory(string $category): array
    {
        $products = $this->productRepository->findBy(['category' => $category]);
        if (empty($products)) {
            throw new ProductWithNoCategoryException($category);
        }
        return $products;
    }
}