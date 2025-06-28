<?php
namespace App\Exception;

class ProductNotFoundException extends \Exception
{
    public function __construct(string $productName)
    {
        parent::__construct("Product with name '{$productName}' not found.");
    }
}