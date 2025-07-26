<?php

namespace App\Controller;

use App\Dto\Cart\Input\CartInputDto;
use App\Dto\Cart\Input\CartItemInputDto;
use App\Dto\Cart\Output\CartOutputDto;
use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/api/cart/item', methods: ['PUT'])]
    public function updateCartItemQuantity(
        #[CurrentUser] ?User $user,
        Request $request,
        CartService $cartService,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;

        if (!$productId) {
            return $this->json(['error' => 'Missing productId'], 400);
        }

        $cart = $cartService->getOrCreateCartForUser($user);
        $item = $cart->getCartItems()->filter(fn($i) => $i->getProduct()->getId() === $productId)->first();

        if (!$item) {
            return $this->json(['error' => 'Item not found in cart'], 404);
        }

        $newQuantity = max(0, $item->getQuantity() - 1);

        if ($newQuantity === 0) {
            $cart->removeItem($item);
        } else {
            $item->setQuantity($newQuantity);
        }
        $entityManager->flush();

        return $this->json(['status' => 'updated']);
        return $this->json(['status' => 'updated']);
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
    CartService $cartService,
    EntityManagerInterface $em,
    ProductRepository $productRepository
): JsonResponse {
    if (!$user) {
        return $this->json(['error' => 'Unauthorized'], 401);
    }

    $data = json_decode($request->getContent(), true);
    $productId = $data['productId'] ?? null;
    $quantity = $data['quantity'] ?? 1;

    if (!$productId) {
        return $this->json(['error' => 'Missing productId'], 400);
    }

    $product = $productRepository->find($productId);
    if (!$product) {
        return $this->json(['error' => 'Product not found'], 404);
    }

    $cart = $cartService->getOrCreateCartForUser($user);

    $existingItem = $cart->getCartItems()->filter(
        fn($item) => $item->getProduct()->getId() === $productId
    )->first();

    if ($existingItem) {
        $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
    } else {
        $newItem = new CartItem();
        $newItem->setProduct($product);
        $newItem->setQuantity($quantity);
        $cart->addItem($newItem);
    }

    $em->flush();

    return $this->json(['status' => 'item added']);
}

        
}