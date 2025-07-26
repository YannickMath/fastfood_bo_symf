<?php

namespace App\Dto\Cart\Output;

use App\Entity\CartItem;

class CartItemOutputDto
{
    public function __construct(
        public int $productId,
        public string $productName,
        public int $productPrice,
        public int $quantity,
    ) {}

    public static function fromEntity(CartItem $item): self
    {
        $product = $item->getProduct();

        return new self(
            productId: $product->getId(),
            productName: $product->getName(),
             productPrice: (int) $product->getPrice(),
            quantity: $item->getQuantity()
        );
    }
}