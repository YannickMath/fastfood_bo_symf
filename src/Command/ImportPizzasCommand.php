<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Burger;
use App\Entity\Pizza;

#[AsCommand(
    name: 'app:import-pizzas',
    description: 'Add a short description for your command',
)]

class ImportPizzasCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }


  public function __invoke(SymfonyStyle $io): int
    {
        $io->section('Import des pizzas');

        $filePath = 'data/pizzas.csv';
        if (!file_exists($filePath)) {
            $io->error('Le fichier pizzas.csv n\'existe pas.');
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $io->error('Impossible d\'ouvrir le fichier pizzas.csv.');
            return Command::FAILURE;
        }  
        
        $header = fgetcsv($handle, 1000, ',');
        if ($header === false) {
            $io->error('Le fichier pizzas.csv est vide ou mal formé.');
            fclose($handle);
            return Command::FAILURE;
        }

        $pizzas = [];
        // Read CSV rows into $pizzas array
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $pizzas[] = array_combine($header, $data);
        }
        fclose($handle);

        foreach ($pizzas as $pizzasData) {

            $existingBurger = $this->entityManager->getRepository(Pizza::class)->findOneBy(['name' => $pizzasData['name']]);
            if ($existingBurger) {
                $io->warning(sprintf('La pizza %s existe déjà, elle ne sera pas importée.', $pizzasData['name']));
                continue;
            }

            $io->info(sprintf('Pizza importée : %s - %s', $pizzasData['name'], $pizzasData['price']));
            
            $burger = new Pizza();
            $burger->setName($pizzasData['name']);
            $burger->setDescription($pizzasData['description']);
            $burger->setPrice((int)$pizzasData['price']);
            $burger->setCreatedAt(new \DateTimeImmutable());
            $burger->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($burger);
        }
        
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}