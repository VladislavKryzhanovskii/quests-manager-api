<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixture extends Fixture
{
    /** @var string */
    public const REFERENCE = 'user';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $user = (new User())->setName($faker->userName());

        $manager->persist($user);
        $manager->flush();

        $this->setReference(self::REFERENCE, $user);
    }
}
