<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedPath;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['details'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    #[Groups(['details'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['details'])]
    private ?int $balance = 0;

    /**
     * @var Collection&Selectable<int, QuestCompletionHistory>
     */
    #[ORM\OneToMany(targetEntity: QuestCompletionHistory::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection&Selectable $questCompletionHistories;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Context(context: [DateTimeNormalizer::FORMAT_KEY => 'm/d/Y H:i:s'])]
    private ?\DateTimeInterface $updateDate;

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

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): static
    {
        $this->balance = $balance;

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
            $questCompletionHistory->setUser($this);
        }

        return $this;
    }

    public function removeQuestCompletionHistory(QuestCompletionHistory $questCompletionHistory): static
    {
        if ($this->questCompletionHistories->removeElement($questCompletionHistory)) {
            if ($questCompletionHistory->getUser() === $this) {
                $questCompletionHistory->setUser(null);
            }
        }

        return $this;
    }
}
