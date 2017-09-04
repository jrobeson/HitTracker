<?php

namespace LazerBall\HitTracker\GameBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\View\View;
use GuzzleHttp\Client;
use LazerBall\HitTracker\Model\Game;
use LazerBall\HitTracker\Model\NewGameData;
use LazerBall\HitTracker\Model\Player;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GameController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::CREATE);

        $vests = $this->get('hittracker.repository.vest')->findActiveVests();

        $newGameData = new NewGameData();
        $players = new ArrayCollection();
        foreach ($vests as $vest) {
            $playerData = new \LazerBall\HitTracker\Model\PlayerData();
            $playerData->name = '';
            $newGameData->addPlayer($playerData);
        }
        $formFactory = new DtoFormFactory($this->container->get('form.factory'));
        $form = $formFactory->create($configuration, $newGameData);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $newResource = new Game($newGameData->gameType, $newGameData->gameLength, $newGameData->settings, $newGameData->arena);
            foreach ($newGameData->players as $player) {
                $newResource->addPlayer(new Player($player->name, $player->unit, $player->hitPoints, $player->team));
            }

            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::CREATE, $configuration, $newResource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                return $this->redirectHandler->redirectToIndex($configuration, $newResource);
            }

            if ($configuration->hasStateMachine()) {
                $this->stateMachine->apply($configuration, $newResource);
            }

            $this->repository->add($newResource);
            $this->eventDispatcher->dispatchPostEvent(ResourceActions::CREATE, $configuration, $newResource);

            if (!$configuration->isHtmlRequest()) {
                return $this->viewHandler->handle($configuration, View::create($newResource, Response::HTTP_CREATED));
            }

            $this->flashHelper->addSuccessFlash($configuration, ResourceActions::CREATE, $newResource);

            return $this->redirectHandler->redirectToResource($configuration, $newResource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        if (!isset($newResource)) {
            $newResource = null;
        }
        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $newResource,
                $this->metadata->getName() => $newResource,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate(ResourceActions::CREATE . '.html'))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * Another version of showAction that allows showing a blank page instead of 404ing
     * Needs to be kept in sync until we can discuss this issue with the Sylius folks
     */
    public function showBlankAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);

        // was $resource = $this->findOr404($configuration);
        $resource = $this->singleResourceProvider->get($configuration, $this->repository);

        if (null !== $resource) {
            $this->eventDispatcher->dispatch(ResourceActions::SHOW, $configuration, $resource);
        }
        // took a $resource argument
        $view = View::create($resource);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate(ResourceActions::SHOW . '.html'))
                ->setTemplateVar($this->metadata->getName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resource' => $resource,
                    $this->metadata->getName() => $resource,
                ])
            ;
        }

        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * Stop the game
     */
    public function stopAction(Request $request): RedirectResponse
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->findOr404($configuration);

        $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

        if ($event->isStopped() && !$configuration->isHtmlRequest()) {
            throw new HttpException($event->getErrorCode(), $event->getMessage());
        }

        if ($resource) {
            $resource->stop();
            $this->manager->persist($resource);
            $this->manager->flush();
            $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

            $data = [
                'id' => $resource->getId(),
                'arena' => $resource->getArena(),
                'created_at' => $resource->getCreatedAt()->getTimestamp(),
                'ends_at' => $resource->getEndsAt()->getTimestamp(),
            ];
            $pubSub = $this->get('hittracker_pubsub.handler');
            $pubSub->publish('game.end', $data);

            $url = 'http://localhost:3000/stop';
            $httpClient = new Client();
            $radioIds = [];
            foreach ($resource->getPlayers() as $player) {
                $radioIds[] = $player->getUnit()->getRadioId();
            }
            $gameConfiguration = [
                'radioIds' => $radioIds,
                'hitUrl' => $this->generateUrl('hittracker_game_hit')
            ];

            $httpClient->post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $gameConfiguration,
            ]);

            $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);
        }

        return $this->redirect($this->generateUrl('hittracker_game_create'));
    }

    /**
     * Register a hit
     *
     * @todo make it a real API
     *
     * @return JsonResponse
     */
    public function hitAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);

        $games = $this->repository->getActiveGames();

        if (empty($games)) {
            return new JsonResponse(['error' => 'no active games'], 404);
        }
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !array_key_exists('events', $data)) {
            return new JsonResponse(['error' => 'malformed request'], 400);
        }
        foreach ($data['events'] as $data) {
            $event = $data['event'];
            $game = null;
            $player = null;
            if (!empty($data['radioId'])) {
                foreach ($games as $g) {
                    // @todo check valid radio ids
                    $player = $g->getPlayerByRadioId($data['radioId']);
                    if ($player) {
                        $game = $g;
                    }
                }
            }
            if (!$player || !$game) {
                continue;
            }
            $gameSettings = $game->getSettings();
            switch ($event) {
                case 'hit':
                    // @todo return an error if zone isn't set
                    $zone = $data['zone'] ?? null;
                    $player->hit($zone, $gameSettings->getPlayerScorePerHit(), $gameSettings->getPlayerHitPointsDeducted());
                    $this->notify('hit', $game, $player, $zone);
                    break;
            }

            $this->getDoctrine()->getManager()->persist($player);
        }
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([], 200);
    }

    public function notify(string $event, $game, $player, $zone = null)
    {
        $data = [
            'event' => $event,
            'target_player' => [
                'id' => $player->getId(),
                'name' => $player->getName(),
                'team' => $player->getTeam(),
                'zone' => $zone,
            ],
        ];

        if ('hit' === $event) {
            $data['target_player']['hit_points'] = $player->getHitPoints();
            $data['target_player']['score'] = $player->getScore();
            $data['target_team_hit_points'] = $game->getTeamHitPoints($player->getTeam());
            $data['target_team_score'] = $game->getTeamScore($player->getTeam());
        }
        if ($zone) {
            $data['target_player']['zone_hits'] = $player->hitsInZone($zone);
        }
        $pubSub = $this->get('hittracker_pubsub.handler');
        $pubSub->publish('game.hit', $data);
    }
}
