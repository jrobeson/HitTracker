<?php

namespace App\GameBundle\Controller;

use App\Model\Game;
use App\Model\MatchTeam;
use App\Model\NewGameData;
use App\Model\Player;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\View\View;
use GuzzleHttp\Client;
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
        $newResource = null;
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::CREATE);

        $vests = $this->get('hittracker.repository.vest')->findVestsGroupedByColor();
        $newGameData = new NewGameData();
        $players = new ArrayCollection();
        foreach ($vests as $teamColor => $teamVests) {
            foreach ((array) $teamVests as $teamVest) {
                $playerData = new \App\Model\PlayerData();
                $team = 1;
                if (null !== $teamVest && (in_array($teamColor, ['orange', 'red']))) {
                    $team = 2;
                }
                $playerData->name = '';
                $team = 'Team '. $team;

                $newGameData->addPlayer($playerData, (string) $team, $teamVest->getColor());
            }
        }
        $formFactory = new DtoFormFactory($this->container->get('form.factory'));
        $form = $formFactory->create($configuration, $newGameData);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $newResource = new Game($newGameData->gameType, $newGameData->gameLength, $newGameData->settings, $newGameData->getArena());
            foreach ($newGameData->teams as $teamNo => $team) {
                $matchTeam = new MatchTeam($team['name'], $team['color']);
                foreach ($team['players'] as $player) {
                    $matchTeam->addPlayer(new Player($player->name, $player->unit, $player->hitPoints));
                }
                $newResource->addTeam($matchTeam);
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

        if (null !== $resource) {
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

            $httpClient = new Client();
            $radioIds = [];
            foreach ($resource->getPlayers() as $player) {
                $radioIds[] = $player->getUnit()->getRadioId();
            }
            $gameConfiguration = [
                'radioIds' => $radioIds,
                'hitUrl' => $this->generateUrl('hittracker_game_hit')
            ];

            $httpClient->post($request->getSchemeAndHttpHost().'/blegateway/stop', [
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
     */
    public function hitAction(Request $request): JsonResponse
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
        foreach ($data['events'] as $eventData) {
            $event = $eventData['event'];
            $game = null;
            $player = null;
            if (!empty($eventData['radioId'])) {
                foreach ($games as $g) {
                    // @todo check valid radio ids
                    $player = $g->getPlayerByRadioId($eventData['radioId']);
                    if ($player) {
                        $game = $g;
                    }
                }
            }
            if (!$player || !$game) {
                continue;
            }
            $matchSettings = $game->getSettings();
            switch ($event) {
                case 'hit':
                    // @todo return an error if zone isn't set
                    $zone = $eventData['zone'] ?? null;
                    $player->hit($zone, $matchSettings->getPlayerScorePerHit(), $matchSettings->getPlayerHitPointsDeducted());
                    $this->notify('hit', $game, $player, $zone);
                    break;
            }

            $this->getDoctrine()->getManager()->persist($player);
        }
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([], 200);
    }

    public function notify(string $event, Game $game, Player $player, ?int $zone = null): void
    {
        $data = [
            'event' => $event,
            'playerData' => [
                'id' => $player->getId(),
            ],
        ];

        if ('hit' === $event) {
            $data['playerData']['hitPoints'] = $player->getHitPoints();
            $data['playerData']['score'] = $player->getScore();
            $data['teamData']['hitPoints'] = $player->getTeam()->getHitPoints();
            $data['teamData']['id'] = $player->getTeam()->getId();
            $data['teamData']['score'] = $player->getTeam()->getScore();
        }
        if ($zone) {
            $data['playerData']['zoneHits'] = $player->zoneHits;
        }
        $pubSub = $this->get('hittracker_pubsub.handler');
        $pubSub->publish('game.hit', $data);
    }
}
