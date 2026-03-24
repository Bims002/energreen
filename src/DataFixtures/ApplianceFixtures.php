<?php

namespace App\DataFixtures;

use App\Entity\Appliance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ApplianceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // On liste les appareils avec leurs caractéristiques
        $appliancesData = [
            ['Television', 100, 4.0, 'Standard', 4.0],
            ['Refrigerateur', 150, 24.0, 'Standard', 24.0],
            ['Congelateur Coffre', 200, 24.0, 'Standard', 24.0],
            ['Ordinateur de bureau', 250, 6.0, 'Standard', 6.0],
            ['Ordinateur portable', 50, 5.0, 'Standard', 5.0],
            ['Smartphone/tablette', 10, 2.0, 'Charge', 2.0],
            ['Modem DSL', 15, 24.0, 'Standard', 24.0],
            ['Decodeur', 20, 4.0, 'Standard', 4.0],
            ['Console de jeu', 150, 3.0, 'Gaming', 3.0],
            ['Lave-linge', 2000, 1.5, 'Coton 40°', 1.5],
            ['seche-linge', 2500, 1.5, 'Coton Prêt à ranger', 1.5],
            ['Lave-vaisselle', 1200, 2.0, 'Eco', 2.0],
            ['Four électrique', 2500, 1.0, 'Chaleur tournante', 1.0],
            ['Plaque de cuisson', 3000, 0.5, 'Standard', 0.5],
            ['Chaudière', 150, 24.0, 'Hiver', 24.0],
            ['Radiateur électrique', 1500, 8.0, 'Standard', 8.0],
            ['Ventilateur', 50, 4.0, 'Vitesse 2', 4.0],
            ['Eclairage (Ampoules)', 60, 5.0, 'Standard', 5.0],
        ];

        foreach ($appliancesData as $data) {
            $appliance = new Appliance();
            $appliance->setName($data[0]);
            $appliance->setPower($data[1]);
            $appliance->setUsageAppliance($data[2]);
            $appliance->setMode($data[3]);
            $appliance->setDuration($data[4]);

            $manager->persist($appliance);
        }

        $manager->flush();
    }
}