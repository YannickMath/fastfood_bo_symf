<?php

namespace App\Service;

use App\Dto\Cart\Input\CartInputDto;
use App\Dto\Cart\Input\CartItemInputDto;
use App\Dto\Cart\Output\CartItemOutputDto;
use App\Dto\Cart\Output\CartOutputDto;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getCartEntityForUser(User $user): ?Cart
    {
        return $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
    }

    public function getCartForUser(User $user): ?CartOutputDto
    {
        $cart = $this->getCartEntityForUser($user);
        if (!$cart) {
            return null;
        }

        $items = [];

        foreach ($cart->getCartItems() as $item) {
            $items[] = CartItemOutputDto::fromEntity($item);
        }

        return new CartOutputDto($items);
    }

    public function createCartWithItems(User $user, CartInputDto $dto): CartOutputDto
    {
        $cart = new Cart();
        $cart->setUser($user);

        foreach ($dto->items as $itemDto) {
            if (!$itemDto instanceof \App\Dto\Cart\Input\CartItemInputDto) {
                throw new \InvalidArgumentException("Invalid item in CartInputDto");
            }

            $product = $this->entityManager->getRepository(Product::class)->find($itemDto->productId);

            if (!$product) {
                throw new \Exception("Product with ID {$itemDto->productId} not found");
            }

            $item = new CartItem();
            $item->setProduct($product);
            $item->setQuantity($itemDto->quantity);

            $cart->addItem($item);
        }

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $this->getCartForUser($user);
    }


    public function updateCartWithItems(Cart $cart, CartInputDto $dto): CartOutputDto
    {
        $cart->clearItems();

        foreach ($dto->items as $itemDto) {
            $product = $this->entityManager->getRepository(Product::class)->find($itemDto->productId);

            if (!$product) {
                throw new \InvalidArgumentException("Product not found with ID " . $itemDto->productId);
            }

            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($itemDto->quantity);
            $cartItem->setCart($cart);

            $cart->addItem($cartItem);
        }

        $this->entityManager->flush();

        return $this->buildCartOutputDto($cart);
    }

    public function getOrCreateCartForUser(User $user): Cart
    {
        $cart = $this->getCartEntityForUser($user);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        return $cart;
    }

    public function addOrUpdateItem(Cart $cart, CartItemInputDto $itemDto): Cart
    {
        $existingItem = $cart->getCartItems()->filter(fn(CartItem $item) => $item->getProduct()->getId() === $itemDto->productId)->first();

        if ($existingItem) {
            $existingItem->setQuantity($itemDto->quantity);
        } else {
            $product = $this->entityManager->getRepository(Product::class)->find($itemDto->productId);
            if (!$product) {
                throw new \Exception("Product with ID {$itemDto->productId} not found");
            }

            $newItem = new CartItem();
            $newItem->setProduct($product);
            $newItem->setQuantity($itemDto->quantity);
            $cart->addItem($newItem);
        }

        $this->entityManager->flush();

        return $cart;
    }

    public function buildCartOutputDto(Cart $cart): CartOutputDto
    {
        $items = [];

        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();

            $items[] = new CartItemOutputDto(
                $cartItem->getId(),
                $product->getId(),
                $product->getName(),
                (float) $product->getPrice(),
                $cartItem->getQuantity()
            );
        }

        return new CartOutputDto($items);
    }

    public function clearCart(Cart $cart): void
    {
        $items = $cart->getCartItems()->toArray();
        foreach ($items as $item) {
            $cart->removeItem($item);
        }

        $this->entityManager->flush();
    }

}