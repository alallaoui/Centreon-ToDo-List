<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Faker\Factory;
use Faker\Generator;
use App\Entity\Task;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create('fr_FR');

        // Creation de 20 fixtures 
        for ($i = 0; $i < 21; $i++) {

        $task= (new Task())
        ->setTitle($this->faker->realText('50'))
        ->setStatus($this->faker->randomElement([
            'A faire', 'En cours', 'terminée'
        ]))
        ->setDescription($this->faker->realText('200'));
        
         $manager->persist($task);
        }
        
        $manager->flush();
    }
}
