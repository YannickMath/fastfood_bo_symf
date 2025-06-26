<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Burger;

#[AsCommand(
    name: 'app:import-burgers',
    description: 'Add a short description for your command',
)]

class ImportBurgersCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }


  public function __invoke(SymfonyStyle $io): int
    {
        $io->section('Import des burgers');

        $filePath = 'data/burgers.csv';
        if (!file_exists($filePath)) {
            $io->error('Le fichier burgers.csv n\'existe pas.');
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $io->error('Impossible d\'ouvrir le fichier burgers.csv.');
            return Command::FAILURE;
        }  
        
        $header = fgetcsv($handle, 1000, ',');
        if ($header === false) {
            $io->error('Le fichier burgers.csv est vide ou mal formé.');
            fclose($handle);
            return Command::FAILURE;
        }

        $burgers = [];
        // Read CSV rows into $burgers array
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $burgers[] = array_combine($header, $data);
        }
        fclose($handle);

        foreach ($burgers as $burgerData) {

            $existingBurger = $this->entityManager->getRepository(Burger::class)->findOneBy(['name' => $burgerData['name']]);
            if ($existingBurger) {
                $io->warning(sprintf('Le burger %s existe déjà, il ne sera pas importé.', $burgerData['name']));
                continue;
            }

            $io->info(sprintf('Burger importé : %s - %s', $burgerData['name'], $burgerData['price']));
            
            $burger = new Burger();
            $burger->setName($burgerData['name']);
            $burger->setDescription($burgerData['description']);
            $burger->setPrice((int)$burgerData['price']);
            $burger->setCreatedAt(new \DateTimeImmutable());
            $burger->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($burger);
        }
        
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}