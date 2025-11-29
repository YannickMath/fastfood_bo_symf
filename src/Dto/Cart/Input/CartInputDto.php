<?php
namespace App\Dto\Cart\Input;

use Symfony\Component\Validator\Constraints as Assert;

class CartInputDto
{
    /**
     * @var CartItemInputDto[]
     */
    #[Assert\NotBlank(message: "Cart cannot be empty")]
    #[Assert\Valid]
    public array $items = [];
}