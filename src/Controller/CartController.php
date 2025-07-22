<?php

namespace App\Controller;

use App\Dto\Cart\Input\CartInputDto;
use App\Dto\Cart\Input\CartItemInputDto;
use App\Dto\Cart\Output\CartOutputDto;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CartController extends AbstractController
{
    #[Route('/api/cart', methods: ['GET'])]
    public function getCart(
        #[CurrentUser] ?User $user,
        CartService $cartService
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $cartDto = $cartService->getCartForUser($user);
        return $this->json($cartDto ?? []);
    }

    #[Route('/api/cart', methods: ['POST'])]
    public function saveCart(
        #[CurrentUser] ?User $user,
        Request $request,
        CartService $cartService,
        ValidatorInterface $validator
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        // dd("data",$data);
        $dto = CartInputDto::fromArray($data);
        
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $existingCart = $cartService->getCartEntityForUser($user);

        $cartDto = $existingCart
            ? $cartService->updateCartWithItems($existingCart, $dto)
            : $cartService->createCartWithItems($user, $dto);

        return $this->json($cartDto);
    }

    #[Route('/api/cart', methods: ['DELETE'])]
    public function clearCart(
        #[CurrentUser] ?User $user,
        CartService $cartService
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $cart = $cartService->getCartEntityForUser($user);
        if ($cart) {
            $cartService->clearCart($cart);
        }

        return $this->json(['status' => 'cleared']);
    }

    #[Route('/api/cart/items', methods: ['DELETE'])]
    public function clearCartItems(
        #[CurrentUser] ?User $user,
        CartService $cartService
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $cart = $cartService->getCartEntityForUser($user);
        if ($cart) {
            foreach ($cart->getCartItems() as $item) {
                $cart->removeItem($item);
            }
            $cartService->updateCartWithItems($cart, new CartInputDto(['items' => []]));
        }

        return $this->json(['status' => 'items cleared']);
    }
    
    #[Route('/api/cart/items', methods: ['POST'])]
    public function addCartItem(
        #[CurrentUser] ?User $user,
        Request $request,
        ProductRepository $productRepository,
        CartService $cartService
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        
        // Sécurité : attend uniquement un produitId + quantity
        $productId = $data['productId'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        if (!$productId) {
            return $this->json(['error' => 'Missing productId'], 400);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $cartItemDto = new CartItemInputDto(
            quantity: $quantity,
            productId: $product->getId(),
        );

        $cart = $cartService->getOrCreateCartForUser($user);
        $updatedCart = $cartService->addOrUpdateItem($cart, $cartItemDto);

        return $this->json(CartOutputDto::fromEntity($updatedCart));

    }
        
}