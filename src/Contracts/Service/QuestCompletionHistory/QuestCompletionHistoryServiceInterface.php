<?php

declare(strict_types=1);

namespace App\Contracts\Service\QuestCompletionHistory;

use App\Entity\Quest;
use App\Entity\User;

interface QuestCompletionHistoryServiceInterface
{
    public function completeQuest(User $user, Quest $quest): void;
}