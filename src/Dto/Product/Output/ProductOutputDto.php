<?php

namespace App\Dto\Product\Output;

use Symfony\Component\Validator\Constraints as Assert;

class ProductOutputDto
{
    #[Assert\NotBlank]
    public int $id;

    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $price;

    #[Assert\Length(max: 500)]
    public ?string $description = null;

    #[Assert\NotBlank]
    public int $category;

    public function __construct(int $id, string $name, int $price, ?string $description = null, int $category)
    {   
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->category = $category;
    }
}