<?php

namespace App\Dto\Product\Input;

use Symfony\Component\Validator\Constraints as Assert;
class ProductInputDto
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $price;

    #[Assert\Length(max: 500)]
    public string $description;

    #[Assert\Length(max: 255)]
    public string $category;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->price = $data['price'] ?? 0.0;
        $this->description = $data['description'] ?? null;
        $this->category = $data['category'] ?? '';
        
    }

}