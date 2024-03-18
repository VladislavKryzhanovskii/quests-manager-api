<?php

declare(strict_types=1);

namespace App\Service\QuestCompletionHistory;

use App\Contracts\Service\QuestCompletionHistory\QuestCompletionHistoryServiceInterface;
use App\Entity\Quest;
use App\Entity\QuestCompletionHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Psr\Log\LoggerInterface;

readonly class QuestCompletionHistoryService implements QuestCompletionHistoryServiceInterface
{
    public function __construct(
        /** @var EntityManager */
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger,
    )
    {
    }

    public function completeQuest(User $user, Quest $quest): void
    {
        $history = (new QuestCompletionHistory())
            ->setCompleteDate(new \DateTimeImmutable())
            ->setUser($user)
            ->setQuest($quest);

        $user->setBalance($user->getBalance() + $quest->getCost());
        try {
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        }
    }
}