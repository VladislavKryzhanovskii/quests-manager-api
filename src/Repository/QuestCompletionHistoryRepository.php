<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QuestCompletionHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestCompletionHistory>
 *
 * @method QuestCompletionHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuestCompletionHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuestCompletionHistory[]    findAll()
 * @method QuestCompletionHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestCompletionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestCompletionHistory::class);
    }

}
