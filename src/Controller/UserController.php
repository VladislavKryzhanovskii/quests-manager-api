<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contracts\Service\QuestCompletionHistory\QuestCompletionHistoryServiceInterface;
use App\Contracts\Service\User\UserServiceInterface;
use App\Entity\Quest;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users', name: 'user_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserServiceInterface                   $userService,
        private readonly SerializerInterface                    $serializer,
        private readonly ValidatorInterface                     $validator,
        private readonly QuestCompletionHistoryServiceInterface $historyService,
    )
    {
    }


    #[Route('/', name: 'collection', methods: [Request::METHOD_GET])]
    public function get(Request $request): JsonResponse
    {
        $paginator = $this->userService->get();

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

    #[Route('/{id<\d+>}', name: 'find', methods: [Request::METHOD_GET])]
    public function find(User $user): JsonResponse
    {
        $json = $this->serializer->serialize($user, 'json', [AbstractNormalizer::GROUPS => 'details']);
        return JsonResponse::fromJsonString($json);
    }


    #[Route('/', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(['message' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        $this->userService->save($user);

        return $this->json(['id' => $user->getId()], Response::HTTP_CREATED);
    }


    #[Route('/{id<\d+>}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id<\d+>}', name: 'patch', methods: [Request::METHOD_PATCH, Request::METHOD_PUT])]
    public function update(User $user, Request $request): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(['message' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }
        $this->userService->save($user);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id<\d+>}/quests/{questId<\d+>}', name: 'complete_quest', methods: [Request::METHOD_PATCH])]
    public function completeQuest(User $user, #[MapEntity(mapping: ['questId' => 'id'])] Quest $quest): JsonResponse
    {
        $this->historyService->completeQuest($user, $quest);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

}
