<?php
/**
 * This file was "borrowed" from KnpLabs/RadBundle
 *
 * {@link https://github.com/KnpLabs/KnpRadBundle}
 *
 * @license MIT
 */

namespace LazerBall\HitTracker\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfListener
{
    private $csrfTokenManager;
    /** @var string */
    private $csrfTokenId;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager, string $csrfTokenId)
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->csrfTokenId = $csrfTokenId;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (false === $request->attributes->get('_check_csrf', false)) {
            return;
        }
        if (!$request->request->has('_link_token')) {
            throw new \InvalidArgumentException(
                'The CSRF token verification is activated but you did not send a token. Please submit a request with a valid csrf token.'
            );
        }

        $token = $request->request->get('_link_token');

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($this->csrfTokenId, $token))) {
            throw new \InvalidArgumentException(
                'The CSRF token is invalid. Please submit a request with a valid csrf token.'
            );
        }
    }
}
