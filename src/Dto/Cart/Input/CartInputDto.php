<?php

namespace App\Dto\Cart\Input;

class CartInputDto
{
    /** @var CartItemInputDto[] */
    public array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new \InvalidArgumentException("Expected 'items' as an array.");
        }

        $itemsDto = array_map(
            fn(array $item) => new CartItemInputDto(
                $item['productId'],
                $item['quantity']
            ),
            $data['items']
        );

        return new self($itemsDto);
    }
}