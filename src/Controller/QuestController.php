<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contracts\Service\Quest\QuestServiceInterface;
use App\DTO\UpdateQuestDTO;
use App\Entity\Quest;
use App\Tests\Controller\UserControllerTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see UserControllerTest
 */
#[Route('/api/quests', name: 'quest_')]
class QuestController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface    $validator,
        private readonly SerializerInterface   $serializer,
        private readonly QuestServiceInterface $questService,
    )
    {
    }

    #[Route('/', methods: [Request::METHOD_GET])]
    public function get(Request $request): JsonResponse
    {
        $paginator = $this->questService->get();

        $data = [
            'totalCount' => $total = count($paginator),
            'pageCount' => (int)ceil($total / $request->query->getInt('limit', 10)),
            'result' => iterator_to_array($paginator)
        ];

        $json = $this->serializer->serialize($data, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['questCompletionHistories']
        ]);

        return JsonResponse::fromJsonString($json);
    }

    #[Route('/{id<\d+>}', methods: [Request::METHOD_GET])]
    public function find(Quest $quest): JsonResponse
    {
        $json = $this->serializer->serialize($quest, 'json', [AbstractNormalizer::GROUPS => 'details']);
        return JsonResponse::fromJsonString($json);
    }

    #[Route('/', methods: [Request::METHOD_POST])]
    public function save(Request $request): JsonResponse
    {
        /** @var Quest $quest */
        $quest = $this->serializer->deserialize($request->getContent(), Quest::class, 'json');
        $errors = $this->validator->validate($quest);

        if (count($errors) > 0) {
            return $this->json(['message' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        $this->questService->save($quest);

        return $this->json(['id' => $quest->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', methods: [Request::METHOD_DELETE])]
    public function delete(Quest $quest): JsonResponse
    {
        if (!$quest->getQuestCompletionHistories()->isEmpty()) {
            return $this->json(['message' => 'Невозможно удалять задачи с существующей историей выполнения.'], Response::HTTP_BAD_REQUEST);
        }

        $this->questService->delete($quest);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id<\d+>}', methods: [Request::METHOD_PUT])]
    public function update(int $id, Request $request): JsonResponse
    {
        $quest = $this->questService->find($id) ?? new Quest();

        if (!$quest->getQuestCompletionHistories()->isEmpty()) {
            return $this->json(['message' => 'Невозможно изменять задачи с существующей историей выполнения.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var UpdateQuestDTO $dto */
        $dto = $this->serializer->deserialize($request->getContent(), UpdateQuestDTO::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['message' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        $this->questService->update($quest, $dto);

        return $this->json(['id' => $quest->getId()]);
    }

    #[Route('/{id<\d+>}', methods: [Request::METHOD_PATCH])]
    public function patch(Quest $quest, Request $request): JsonResponse
    {
        if (!$quest->getQuestCompletionHistories()->isEmpty()) {
            return $this->json(['message' => 'Невозможно изменять задачи с существующей историей выполнения.'], Response::HTTP_BAD_REQUEST);
        }

        $this->serializer->deserialize($request->getContent(), Quest::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $quest]);
        $errors = $this->validator->validate($quest);

        if (count($errors) > 0) {
            return $this->json(['message' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        $this->questService->save($quest);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }


}
