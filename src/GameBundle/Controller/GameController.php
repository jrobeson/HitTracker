<?php

namespace HitTracker\GameBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;

class GameController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request)
    {
        $resource = $this->createNew();

        foreach (\iter\range(0, 19) as $v) {
            $player = new \HitTracker\GameBundle\Entity\Player();
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

        $game = $this->getRepository()->getActiveGame($arena);

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('show.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData($game)
        ;

        return $this->handleView($view);
    }

    /**
     * @param       $event
     * @param array $data
     */
    public function publish($event, array $data)
    {

        $client = new Client();
        $response = $client->post('http://hittracker.local.lan/publish/game', [
            'headers' => ['Event-Type' => $event],
            'json'    => $data,
        ]);

        echo $response->getBody();
    }

    public function stopAction(Request $request)
    {
        $game = $this->getRepository()->getActiveGame(1);

        if (!$game) {
            return new JsonResponse(['error' => 'no such game'], 404);
        }

        $game->stop();
        $this->getDoctrine()->getManager()->persist($game);
        $this->getDoctrine()->getManager()->flush();

        $data = [
            'arena' => $game->getArena(),
            'created_at' => $game->getCreatedAt()->getTimestamp(),
            'ends_at' => $game->getEndsAt()->getTimestamp(),
        ];
        $this->publish('game.end', $data);

        return new JsonResponse([], 200);
    }

    /**
     * Register a hit
     *
     * @param Request $request
     * @todo move to separate API controller
     * @return JsonResponse
     */
    public function hitAction(Request $request)
    {
        $game = $this->getRepository()->getActiveGame(1);

        if (!$game) {
            return new JsonResponse(['error' => 'no such game'], 404);
        }

        $esn = $request->request->get('esn');
        $zone = (int)$request->request->get('zone');

        $player = $game->getPlayerByEsn($esn);


        $hit = [
            'esn' => $esn,
            'player_id' => $player->getId(),
            'zone' => $zone
        ];

        $dead = $player->hit($zone, $game->getLifeCreditsDeducted());
        if ($dead) {
            return new JsonResponse([], 200);
        }
        $hit['life_credits'] = $player->getLifeCredits();

        $this->publish('game.hit', $hit);

        $this->getDoctrine()->getManager()->persist($player);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([], 200);
    }
}
