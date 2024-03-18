<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\QuestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Quest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['details'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    #[Groups(['details'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Groups(['details'])]
    private ?int $cost = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Context(context: [DateTimeNormalizer::FORMAT_KEY => 'm/d/Y H:i:s'])]
    #[Groups(['details'])]
    private ?\DateTimeInterface $updateDate;

    #[ORM\OneToMany(targetEntity: QuestCompletionHistory::class, mappedBy: 'quest', orphanRemoval: true)]
    private Collection $questCompletionHistories;

    public function __construct()
    {
        $this->questCompletionHistories = new ArrayCollection();
        $this->updateDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): static
    {
        $this->cost = $cost;

        return $this;
    }


    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->updateDate;
    }

    #[ORM\PreUpdate]
    public function setUpdateDate(): void
    {
        $this->updateDate = new \DateTimeImmutable();
    }

    /**
     * @return Collection&Selectable<int, QuestCompletionHistory>
     */
    public function getQuestCompletionHistories(): Collection&Selectable
    {
        return $this->questCompletionHistories;
    }

    public function addQuestCompletionHistory(QuestCompletionHistory $questCompletionHistory): static
    {
        if (!$this->questCompletionHistories->contains($questCompletionHistory)) {
            $this->questCompletionHistories->add($questCompletionHistory);
            $questCompletionHistory->setQuest($this);
        }

        return $this;
    }

    public function removeQuestCompletionHistory(QuestCompletionHistory $questCompletionHistory): static
    {
        if ($this->questCompletionHistories->removeElement($questCompletionHistory)) {
            if ($questCompletionHistory->getQuest() === $this) {
                $questCompletionHistory->setQuest(null);
            }
        }

        return $this;
    }
}
