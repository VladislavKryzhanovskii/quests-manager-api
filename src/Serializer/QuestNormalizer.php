<?php

namespace App\Serializer;

use App\Entity\Quest;
use App\Entity\QuestCompletionHistory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class QuestNormalizer implements NormalizerInterface
{

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
    )
    {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->normalizer->normalize($object, $format, $context);

        if (!isset($context[AbstractNormalizer::GROUPS])) {
            return $normalized;
        }

        return match ($context[AbstractNormalizer::GROUPS]) {
            'details' => $this->handleDetailsGroup($normalized, $object),
            default => $normalized,
        };
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Quest;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Quest::class => true,
            '*' => false
        ];
    }

    private function handleDetailsGroup(array $normalized, Quest $quest): array
    {
        $normalized['users'] = $quest->getQuestCompletionHistories()->map(static fn(QuestCompletionHistory $history): array => [
            'id' => $history->getUser()->getId(),
            'name' => $history->getUser()->getName(),
            'completeDate' => $history->getCompleteDate()->format('m/d/Y H:i:s')
        ])->toArray();

        return $normalized;
    }
}