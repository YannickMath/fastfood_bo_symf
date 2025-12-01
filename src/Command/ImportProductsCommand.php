<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:import-products',
    description: 'Import des produits à partir d\'un fichier CSV.',
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
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $products[] = array_combine($header, $data);
        }
        fclose($handle);

foreach ($products as $productsData) {
    $categoryId = (int) $productsData['category'];
    $category = $this->entityManager
        ->getRepository(Category::class)
        ->find($categoryId);

    if (!$category) {
        $io->warning(sprintf(
            'La catégorie avec l\'ID %d n\'existe pas. Produit "%s" ignoré.',
            $categoryId,
            $productsData['name']
        ));
        continue;
    }

    $existingProduct = $this->entityManager
        ->getRepository(Product::class)
        ->findOneBy(['name' => $productsData['name']]);

    if ($existingProduct) {
        $existingProduct->setDescription($productsData['description']);
        $existingProduct->setPrice((int) $productsData['price']);
        $existingProduct->setCategory($category);
        $existingProduct->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($existingProduct);

        $io->info(sprintf(
            'Produit mis à jour : %s',
            $productsData['name']
        ));
        continue;
    }

    $product = new Product();
    $product->setName($productsData['name']);
    $product->setDescription($productsData['description']);
    $product->setPrice((int) $productsData['price']);
    $product->setCategory($category);
    $product->setCreatedAt(new \DateTimeImmutable());
    $product->setUpdatedAt(new \DateTime());

    $this->entityManager->persist($product);

    $io->info(sprintf(
        'Produit importé : %s - %s€ - catégorie ID %d',
        $productsData['name'],
        $productsData['price'],
        $categoryId
    ));
}

        $this->entityManager->flush();

        $io->success('Import des produits terminé avec succès.');

        return Command::SUCCESS;
    }
}