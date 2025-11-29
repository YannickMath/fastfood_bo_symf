<?php

namespace App\Dto\Cart\Input;

use Symfony\Component\Validator\Constraints as Assert;

class CartItemInputDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $productId,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $quantity
    ) {}
}