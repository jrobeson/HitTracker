<?php
/**
 * This file was "borrowed" from KnpLabs/RadBundle
 *
 * {@link https://github.com/KnpLabs/KnpRadBundle}
 *
 * @license MIT
 */

namespace App\Twig;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig_Extension;
use Twig_Function;

class LinkAttributesExtension extends Twig_Extension
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

    /**
     * @return Twig_Function[]
     */
    public function getFunctions(): array
    {
        return [
            new Twig_Function('link_attr', [$this, 'getLinkAttributes'], ['is_safe' => ['html']]),
            new Twig_Function('link_csrf', [$this, 'getCsrf']),
        ];
    }

    public function getLinkAttributes(string $method, string $confirm = 'Are you sure?'): string
    {
        $html = sprintf('data-method="%s"', $method);

        if ('' !== $confirm) {
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
