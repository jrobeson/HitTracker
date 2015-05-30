<?php

namespace LazerBall\HitTracker\GameBundle\Controller;

use GuzzleHttp\Client;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GameController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request)
    {
        $resource = $this->createNew();

        $vests = $this->get('hittracker.repository.vest')->findActiveVests();

        foreach ($vests as $vest) {
            $player = new \LazerBall\HitTracker\GameBundle\Entity\Player('', $vest);
            $resource->addPlayer($player);
        }

        $form = $this->getForm($resource);

        if ($form->handleRequest($request)->isValid()) {
            $resource = $this->domainManager->create($resource);

            if (null === $resource) {
                return $this->redirectHandler->redirectToIndex();
            }

            $data = [
                'arena' => $resource->getArena(),
                'created_at' => $resource->getCreatedAt()->getTimestamp(),
                'ends_at' => $resource->getEndsAt()->getTimestamp(),
            ];
            $this->publish('game.start', $data);

            return $this->redirectHandler->redirectTo($resource);
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('create.html'))
            ->setData(array(
                $this->config->getResourceName() => $resource,
                'form'                           => $form->createView()
            ))
        ;

        return $this->handleView($view);
    }

    public function activeAction(Request $request)
    {
        $arena = $request->attributes->get('arena');

        $game = $this->getRepository()->getActiveGame($arena);

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('show.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData($game)
        ;

        return $this->handleView($view);
    }

    public function scoreBoardAction(Request $request)
    {
        $arena = $request->attributes->get('arena');

        $game = $this->getRepository()->getMostRecentGame($arena);

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('show.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData($game)
        ;

        return $this->handleView($view);
    }

    /**
     * Show a printable score card
     * @param Request $request
     *
     * @return Response
     */
    public function scoreCardAction(Request $request)
    {
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('scorecard.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData($this->findOr404($request))
        ;

        return $this->handleView($view);
    }

    /**
     * @param       $event
     * @param array $data
     */
    public function publish($event, array $data)
    {
        $url = $this->container->getParameter('hittracker_game.event_handlers.nginx_push_stream.url');
        $client = new Client();
        $client->post($url, [
            'headers' => ['Event-Type' => $event],
            'json'    => $data,
        ]);
    }

    /**
     * Stop the game
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function stopAction($id)
    {
        $game = $this->getRepository()->find($id);

        if ($game) {
            $game->stop();
            $this->getDoctrine()->getManager()->persist($game);
            $this->getDoctrine()->getManager()->flush();

            $data = [
                'id'         => $game->getId(),
                'arena'      => $game->getArena(),
                'created_at' => $game->getCreatedAt()->getTimestamp(),
                'ends_at'    => $game->getEndsAt()->getTimestamp(),
            ];
            $this->publish('game.end', $data);
        }

        return $this->redirect($this->generateUrl('hittracker_game_create'));
    }

    /**
     * Register a hit
     *
     * @param Request $request
     * @todo make it a real API
     * @todo does not work with more than one arena
     * @return JsonResponse
     */
    public function hitAction(Request $request)
    {
        $game = $this->getRepository()->getActiveGame(1);

        if (!$game) {
            return new JsonResponse(['error' => 'no such game'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $radioId = $data['hit']['radioId'];
        $zone = (int) $data['hit']['zone'];

        $player = $game->getPlayerByRadioId($radioId);

        if (!$player) {
            return new JsonResponse(['error' => 'no such player'], 404);
        }

        $player->hit($zone, $game->getPlayerHitPointsDeducted());

        $hit = [
            'target_player' => [
                'id' => $player->getId(),
                'name' => $player->getName(),
                'hit_points' => $player->getHitPoints(),
                'team' => $player->getTeam(),
                'zone' => $zone,
                'zone_hits' => $player->hitsInZone($zone),
            ],
            'target_team_hit_points' => $game->getTeamHitPoints($player->getTeam()),
        ];

        $this->publish('game.hit', $hit);

        $this->getDoctrine()->getManager()->persist($player);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([], 200);
    }
}
