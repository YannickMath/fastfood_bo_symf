<?php

namespace App\Command;

use App\Entity\Category;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:import-categories',
    description: 'Importe les catégories depuis un fichier CSV.',
)]
class ImportCategoriesCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $io->section('Import des catégories');

        $filePath = 'data/categories.csv';
        if (!file_exists($filePath)) {
            $io->error('Le fichier categories.csv n\'existe pas.');
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $io->error('Impossible d\'ouvrir le fichier categories.csv.');
            return Command::FAILURE;
        }

        $header = fgetcsv($handle, 1000, ',');
        if ($header === false || !in_array('type', $header)) {
            $io->error('Le fichier categories.csv est vide ou mal formé (colonne "type" attendue).');
            fclose($handle);
            return Command::FAILURE;
        }

        $categories = [];
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $categories[] = array_combine($header, $data);
        }
        fclose($handle);

        foreach ($categories as $categoryData) {
            $existing = $this->entityManager->getRepository(Category::class)
                ->findOneBy(['type' => $categoryData['type']]);

            if ($existing) {
                $io->warning(sprintf('La catégorie "%s" existe déjà, elle ne sera pas importée.', $categoryData['type']));
                continue;
            }

            $category = new Category();
            $category->setType($categoryData['type']);
            $category->setCreatedAt(new \DateTimeImmutable());
            $category->setUpdatedAt(new \DateTime());

            $this->entityManager->persist($category);

            $io->info(sprintf('Catégorie importée : %s', $categoryData['type']));
        }

        $this->entityManager->flush();

        $io->success('Import des catégories terminé avec succès.');

        return Command::SUCCESS;
    }
}