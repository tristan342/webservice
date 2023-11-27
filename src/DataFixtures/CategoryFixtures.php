<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $categories = ['horror', 'action', 'romance', 'science fiction'];

        foreach ($categories as $category) {
            $category_entity = new Category();
            $category_entity->setName($category);
            $manager->persist($category_entity);
        }

        $manager->flush();
    }
}
