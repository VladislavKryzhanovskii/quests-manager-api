<?php

namespace App\DataFixtures;

use App\Entity\Quest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestFixture extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $quest = (new Quest())
                ->setName('testQuest_' . $i)
                ->setCost(mt_rand(10, 200));
            $manager->persist($quest);
        }
        $manager->flush();
    }
}