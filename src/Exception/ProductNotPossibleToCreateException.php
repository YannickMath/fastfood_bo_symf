<?php
namespace App\Exception;

class ProductNotPossibleToCreateException extends \Exception
{
    public function __construct(string $productName)
    {
        parent::__construct("Product with name '{$productName}' cannot be created. Please check the product details and try again.");
    }
}