<?php

namespace App\DataFixtures;

use App\Entity\Quest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestFixture extends Fixture
{
    /** @var string */
    public const REFERENCE = 'quest';

    public function load(ObjectManager $manager): void
    {
        $quest = (new Quest())
            ->setName(sprintf('testQuest_%s', time()))
            ->setCost(mt_rand(10, 200));
        $manager->persist($quest);
        $manager->flush();

        $this->setReference(self::REFERENCE, $quest);
    }
}