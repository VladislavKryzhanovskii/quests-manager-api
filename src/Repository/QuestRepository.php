<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Quest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quest>
 *
 * @method Quest|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quest|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quest[]    findAll()
 * @method Quest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quest::class);
    }

    public function getPaginatedQuests(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('quest')
            ->orderBy('quest.updateDate','DESC')
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);


        return new Paginator($query, fetchJoinCollection: false);
    }
}
