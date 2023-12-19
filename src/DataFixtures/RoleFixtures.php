<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roleUser = new Role();
        $roleUser->setLabel('ROLE_USER');

        $roleAdmin = new Role();
        $roleAdmin->setLabel('ROLE_ADMIN');

        $manager->persist($roleUser);
        $manager->persist($roleAdmin);

        $manager->flush();
    }
}
