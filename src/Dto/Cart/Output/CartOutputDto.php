<?php

namespace App\Dto\Cart\Output;

class CartOutputDto
{
    /** @var CartItemOutputDto[] */
    public array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromEntity(\App\Entity\Cart $cart): self
    {
        $itemsDto = [];

        foreach ($cart->getCartItems() as $item) {
            $itemsDto[] = CartItemOutputDto::fromEntity($item);
        }

        return new self($itemsDto);
    }
}