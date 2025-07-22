<?php

namespace App\Dto\Cart\Input;

class CartItemInputDto
{
public function __construct(
public int $productId,
public int $quantity
) {}
}