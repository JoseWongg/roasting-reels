<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 * This class can be used to load fixtures into the database. (seed data)
 * See https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
 * It is not used in the project, but it is here for reference.
 * This project uses a different approach to seed data. See the TmdbService class for more details.
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->flush();
    }
}
