<?php

namespace App\Controller;

use App\Dto\Product\Input\ProductInputDto;
use App\Dto\Product\Output\ProductOutputDto;
use App\Service\ProductService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;   
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;  
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;



final class ProductController extends AbstractController
{
    // Route to get all products
    #[Route('/api/products', methods: ['GET'])]
    public function getAllProducts(ProductService $productService): JsonResponse
    {
        $products = $productService->getAllProducts();
        $dtos = [];

        foreach ($products as $product) {
            $dtos[] = new ProductOutputDto($product->getName(), $product->getPrice(), $product->getDescription());
        }
        
        return $this->json($dtos);
    }

    // Route to get one product by ID
    #[Route('/api/product/{id}', methods: ['GET'])]
    public function findProductById (int $id, ProductService $productService): JsonResponse
    {
        $product = $productService->showProduct($id);

        $dto = new ProductOutputDto($product->getName(), $product->getPrice(), $product->getDescription());
        return $this->json($dto);
    }

    // Route to find a product by name
    #[Route('/api/product/find/{name}', methods: ['GET'])]
    public function findProductByName(string $name, ProductService $productService, UserService $userService): JsonResponse
    {
        $user = $this->getUser();
      
        $userService->isGranted($user);
        $product = $productService->findProductByName($name);

        $dto = new ProductOutputDto(
            $product->getName(),
            $product->getPrice(),
            $product->getDescription()
        );

        return $this->json($dto);
    }


    // Route to create a new product
    #[Route('/api/product/create', methods: ['POST'])]
    public function createProduct(
        Request $request,
        ValidatorInterface $validator,
        ProductService $productService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dto = new ProductInputDto($data);
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()][] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorsArray], 400);
        }

        $product = $productService->createProduct($dto);

        return new JsonResponse(
            new ProductOutputDto($product->getName(), $product->getPrice(), $product->getDescription()),
            201
        );
    }

    // Route to update a product
    #[Route('/api/product/update/{id}', methods: ['PUT'])]
    public function updateProduct(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        ProductService $productService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = new ProductInputDto($data);
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()][] = $error->getMessage();
            }
        }

        $product = $productService->updateProduct($id, $dto);


        return new JsonResponse(
            new ProductOutputDto($product->getName(), $product->getPrice(), $product->getDescription()),
            200
        );
    }

    // Route to delete a product by ID
    #[Route('/api/product/delete/{id}', methods: ['DELETE'])]
    public function deleteProduct(int $id, ProductService $productService): JsonResponse
    {
        $productService->deleteProduct($id);

        return new JsonResponse(['status' => 'Product deleted'], 204);
    }

    // Route to get products by category
    #[Route('/api/products/category/{category}', methods: ['GET'])]
    public function getProductsByCategory(string $category, ProductService $productService): JsonResponse
        {
            $category = 
            $products = $productService->getProductsByCategory($category);

            $dtos = [];
            foreach ($products as $product) {
                $dtos[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'description' => $product->getDescription(),
                    'category' => $product->getCategory()
                ];
            }

            return $this->json($dtos); 

        }

}