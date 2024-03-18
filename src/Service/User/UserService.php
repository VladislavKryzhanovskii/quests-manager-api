<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Contracts\Service\User\UserServiceInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        /** @var EntityManager */
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
    )
    {
    }

    public function save(User $user): User
    {
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $user;
    }

    public function delete(User $user): void
    {
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $this->logger->error($exception->getMessage());
        }

    }

    public function get(): Paginator
    {
        $request = $this->requestStack->getCurrentRequest();
        return $this->entityManager->getRepository(User::class)->getPaginatedUsers(
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
    }

    public function find(int $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }
}