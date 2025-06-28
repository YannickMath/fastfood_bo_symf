<?php
namespace App\Exception;

class ProductWithNoCategoryException extends \Exception
{
    public function __construct(string $category)
    {
        parent::__construct("Category does not exist: $category. Please provide a valid category.");
    }
}