<?php

declare(strict_types=1);

namespace App\Contracts\Service\Quest;


use App\DTO\UpdateQuestDTO;
use App\Entity\Quest;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface QuestServiceInterface
{
    public function save(Quest $quest): Quest;

    public function find(int $id): ?Quest;

    /**
     * @return Paginator<int, Quest>
     */
    public function get(): Paginator;

    public function delete(Quest $quest): void;

    public function update(Quest $quest, UpdateQuestDTO $dto): Quest;
}