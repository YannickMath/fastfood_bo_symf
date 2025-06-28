<?php
namespace App\Exception;

class ProductNotDeletedException extends \Exception
{
    public function __construct(string $id)
    {
        parent::__construct("Product with ID $id could not be deleted as it does not exist.");
    }
}