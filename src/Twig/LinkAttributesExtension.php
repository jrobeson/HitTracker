<?php
/**
 * This file was "borrowed" from KnpLabs/RadBundle
 *
 * {@link https://github.com/KnpLabs/KnpRadBundle}
 *
 * @license MIT
 */

namespace LazerBall\HitTracker\Twig;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LinkAttributesExtension extends AbstractExtension
{
    private $csrfTokenManager;
    /** @var string */
    private $csrfTokenId;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager, string $csrfTokenId)
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->csrfTokenId = $csrfTokenId;
    }

    public function getName(): string
    {
        return 'link_attributes';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('link_attr', [$this, 'getLinkAttributes'], ['is_safe' => ['html']]),
            new TwigFunction('link_csrf', [$this, 'getCsrf']),
        ];
    }

    public function getLinkAttributes($method, $confirm = 'Are you sure?'): string
    {
        $html = sprintf('data-method="%s"', $method);

        if (false !== $confirm) {
            $html .= sprintf(' data-confirm="%s"', $confirm);
        } else {
            $html .= ' data-no-confirm';
        }

        return sprintf('%s data-csrf-token="%s"', $html, $this->getCsrf());
    }

    public function getCsrf(): string
    {
        return $this->csrfTokenManager->getToken($this->csrfTokenId);
    }
}
