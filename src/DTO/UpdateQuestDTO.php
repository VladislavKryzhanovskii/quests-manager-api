<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateQuestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3)]
        public string $name,

        #[Assert\NotBlank]
        public int    $cost,
    )
    {
    }
}