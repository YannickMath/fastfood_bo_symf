<?php

namespace App\Dto\Product\Output;

use Symfony\Component\Validator\Constraints as Assert;

class ProductOutputDto
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $price;

    #[Assert\Length(max: 500)]
    public ?string $description = null;

    public function __construct(string $name, int $price, ?string $description = null)
    {
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }
}