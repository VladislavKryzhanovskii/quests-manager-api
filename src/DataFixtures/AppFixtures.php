<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // do nothing
    }

    /**
     * @return class-string<Fixture>[]
     */
    public function getDependencies(): array
    {
        return [
            QuestFixture::class,
            UserFixture::class
        ];
    }
}
