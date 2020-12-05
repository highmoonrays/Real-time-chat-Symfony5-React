<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConversationController
 * @package App\Controller
 * @Route("/conversations", name="conversations.")
 */
class ConversationController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManagerInterface;

    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    /**
     * ConversationController constructor.
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManagerInterface
     * @param ConversationRepository $conversationRepository
     */
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface,
        ConversationRepository $conversationRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManagerInterface = $entityManagerInterface;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/", name="newConversation", methods={"POST"})
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request): Response
    {
        $otherUser = $request->get('otherUser', 0);

        if ($otherUser === null) {
            throw new NotFoundHttpException('not found');
        }

        if ($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception('You cannot create a conversation with yourself');
        }

        $conversation = $this->conversationRepository->findByParticipants($otherUser->getId(), $this->getUser()->getId());

        if (count($conversation)) {
            throw new \Exception('Conversation Already Exists');
        }

        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->entityManagerInterface->getConnection()->beginTransaction();

        try {
            $this->entityManagerInterface->persist($conversation);
            $this->entityManagerInterface->persist($participant);
            $this->entityManagerInterface->persist($otherParticipant);

            $this->entityManagerInterface->flush();
            $this->entityManagerInterface->commit();
        } catch (\Exception $exception) {
            $this->entityManagerInterface->rollback();
            throw $exception;
        }

        return $this->json([
            'id' => $conversation->getId(),
        ], Response::HTTP_CREATED, [], []);
    }

    /**
     * @Route("/", name="getConversations", methods={"GET"})
     */
    public function getConversations()
    {
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());

        return $this->json([$conversations]);
    }

}
