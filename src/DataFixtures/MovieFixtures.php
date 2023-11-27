<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $allCategories = $manager->getRepository(\App\Entity\Category::class)->findAll();

        for ($count = 0; $count < 20; $count++) {
            $movie = new Movie();
            $movie->setTitle("Titre " . $count);
            $movie->setDescription("Description Fixture " . $count);

            $randomDate = new \DateTime();
            $randomDate->modify('-' . mt_rand(1, 30) . ' days');
            $movie->setReleaseDate($randomDate);

            $randomNote = mt_rand(1, 5);
            $movie->setNote($randomNote);

            $randomBoolean = (bool) mt_rand(0, 1);
            $movie->setIsUnderEightTeen($randomBoolean);

            // ramdomCategory
            $randomCategory = $allCategories[mt_rand(0, count($allCategories) - 1)];
            $movie->addCategory($randomCategory);

            $manager->persist($movie);
        }

        $manager->flush();
    }
}
