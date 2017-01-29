<?php

namespace LazerBall\HitTracker\GameBundle\Controller;

use FOS\RestBundle\View\View;
use GuzzleHttp\Client;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GameController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::CREATE);
        $newResource = $this->newResourceFactory->create($configuration, $this->factory);

        $vests = $this->get('hittracker.repository.vest')->findActiveVests();

        foreach ($vests as $vest) {
            $player = new \LazerBall\HitTracker\Model\Player('', $vest);
            $newResource->addPlayer($player);
        }

        $form = $this->resourceFormFactory->create($configuration, $newResource);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $newResource = $form->getData();

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

            $data = [
                'arena' => $newResource->getArena(),
                'created_at' => $newResource->getCreatedAt()->getTimestamp(),
                'ends_at' => $newResource->getEndsAt()->getTimestamp(),
            ];
            $this->publish('game.start', $data);

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
    public function showBlankAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);

        // was $resource = $this->findOr404($configuration);
        $resource = $this->singleResourceProvider->get($configuration, $this->repository);

        if ($resource) {
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
    public function stopAction(Request $request)
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
                'id'         => $resource->getId(),
                'arena'      => $resource->getArena(),
                'created_at' => $resource->getCreatedAt()->getTimestamp(),
                'ends_at'    => $resource->getEndsAt()->getTimestamp(),
            ];
            $this->publish('game.end', $data);
            $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);
        }

        return $this->redirect($this->generateUrl('hittracker_game_create'));
    }

    /**
     * Register a hit
     *
     * @param Request $request
     * @todo make it a real API
     * @return JsonResponse
     */
    public function hitAction(Request $request)
    {
        ini_set('html_errors', 0);
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

            if (!empty($data['radioId'])) {
                foreach ($games as $g) {
                    // @todo check valid radio ids
                    $player = $g->getPlayerByRadioId($data['radioId']);
                    if ($player) {
                        $game = $g;
                    }
                }
            }
            if (!isset($player) || !$player) {
                continue;
            }
            switch ($event) {
                case 'hit':
                    // @todo return an error if zone isn't set
                    if (isset($data['zone'])) {
                        $zone = $data['zone'];
                    }
                    $player->hit($zone, $game->getPlayerHitPointsDeducted());
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

        if ('hit' == $event) {
            $data['target_player']['hit_points'] = $player->getHitPoints();
            $data['target_team_hit_points'] = $game->getTeamHitPoints($player->getTeam());
        }
        if ($zone) {
            $data['target_player']['zone_hits'] = $player->hitsInZone($zone);
        }
        $this->publish('game.hit', $data);
    }
}
