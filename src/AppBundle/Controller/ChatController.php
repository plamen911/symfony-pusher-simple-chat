<?php

namespace AppBundle\Controller;

use AppBundle\Service\Pusher\RealTimeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ChatController
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 */
class ChatController extends Controller
{
    const EVENT_NAME = 'chat-event';

    /** @var RealTimeServiceInterface $realTimeService */
    private $realTimeService;

    /** @var SessionInterface $sessionService */
    private $sessionService;

    /**
     * ChatController constructor.
     * @param RealTimeServiceInterface $realTimeService
     * @param SessionInterface $sessionService
     */
    public function __construct(RealTimeServiceInterface $realTimeService, SessionInterface $sessionService)
    {
        $this->realTimeService = $realTimeService;
        $this->sessionService = $sessionService;
        $this->sessionService->start();
    }

    /**
     * @Route("/show", name="chat_show", methods={"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
    {
        return $this->render('chat/show.html.twig', [
            'event_name' => self::EVENT_NAME,
        ]);
    }

    /**
     * @Route("/submit", name="chat_submit", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAction(Request $request)
    {
        $data = [
            'message' => $request->request->get('message', 0),
            'send_at' => date('Y-m-d H:i:s'),
            'author' => 'user_' . $this->sessionService->getId(),
        ];

        try {
            $this->realTimeService
                ->setEvent(self::EVENT_NAME)
                ->dispatch($data);
        } catch (\Exception $ex) {
            return $this->json(['error' => $ex->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($data, Response::HTTP_OK);
    }
}
