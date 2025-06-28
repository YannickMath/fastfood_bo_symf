<?php
namespace App\Exception;

class ProductCannotBeUpdatedException extends \Exception
{
    public function __construct(string $productName)
    {
        parent::__construct("Product with name '{$productName}' cannot be updated. Please check the product details needed and try again.");
    }
}