<?php

namespace App\Command;

use App\Entity\Product;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:import-products',
    description: 'Add a short description for your command',
)]

class ImportProductsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }


  public function __invoke(SymfonyStyle $io): int
    {
        $io->section('Import des produits');

        $filePath = 'data/products.csv';
        if (!file_exists($filePath)) {
            $io->error('Le fichier products.csv n\'existe pas.');
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $io->error('Impossible d\'ouvrir le fichier products.csv.');
            return Command::FAILURE;
        }  
        
        $header = fgetcsv($handle, 1000, ',');
        if ($header === false) {
            $io->error('Le fichier products.csv est vide ou mal formé.');
            fclose($handle);
            return Command::FAILURE;
        }

        $products = [];
        // Read CSV rows into $pizzas array
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $products[] = array_combine($header, $data);
        }
        fclose($handle);

        foreach ($products as $productsData) {

            $existingProduct = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $productsData['name']]);
            if ($existingProduct) {
                $io->warning(sprintf('Le produit %s existe déjà, il ne sera pas importé.', $productsData['name']));
                continue;
            }

            $io->info(sprintf('Produit importé : %s - %s', $productsData['name'], $productsData['price'], $productsData['category']));
            
            $burger = new Product();
            $burger->setName($productsData['name']);
            $burger->setDescription($productsData['description']);
            $burger->setPrice((int)$productsData['price']);
            $burger->setCategory($productsData['category']);
            $burger->setCreatedAt(new \DateTimeImmutable());
            $burger->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($burger);
        }
        
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}