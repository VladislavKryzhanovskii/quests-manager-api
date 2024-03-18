<?php

declare(strict_types=1);

namespace App\Service\Quest;

use App\Contracts\Service\Quest\QuestServiceInterface;
use App\DTO\UpdateQuestDTO;
use App\Entity\Quest;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class QuestService implements QuestServiceInterface
{
    public function __construct(
        /** @var EntityManager */
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger,
        private RequestStack           $requestStack,
    )
    {
    }

    public function save(Quest $quest): Quest
    {
        try {
            $this->entityManager->persist($quest);
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $this->logger->error($exception);
        }

        return $quest;
    }

    public function find(int $id): ?Quest
    {
        return $this->entityManager->find(Quest::class, $id);
    }

    public function get(): Paginator
    {
        $request = $this->requestStack->getCurrentRequest();
        return $this->entityManager->getRepository(Quest::class)->getPaginatedQuests(
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
    }

    public function delete(Quest $quest): void
    {
        try {
            $this->entityManager->remove($quest);
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $this->logger->error($exception);
        }
    }

    public function update(Quest $quest, UpdateQuestDTO $dto): Quest
    {
        $quest->setName($dto->name)->setCost($dto->cost);

        return $this->save($quest);
    }
}