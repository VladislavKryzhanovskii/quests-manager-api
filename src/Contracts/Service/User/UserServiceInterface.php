<?php

declare(strict_types=1);

namespace App\Contracts\Service\User;


use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface UserServiceInterface
{
    public function save(User $user): User;

    public function delete(User $user): void;

    /**
     * @return Paginator<int, User>
     */
    public function get(): Paginator;

    public function find(int $id): ?User;
}