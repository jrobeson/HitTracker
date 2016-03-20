<?php
/**
 * This file was "borrowed" from KnpLabs/RadBundle
 * @license MIT
 * @see https://github.com/KnpLabs/KnpRadBundle/
 */
namespace LazerBall\HitTracker\CommonBundle\Twig;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class LinkAttributesExtension extends \Twig_Extension
{
    private $csrfTokenManager;
    private $csrfTokenId;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager, $csrfTokenId = 'link')
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->csrfTokenId = $csrfTokenId;
    }

    public function getName()
    {
        return 'link_attributes';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('link_attr', [$this, 'getLinkAttributes'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('link_csrf', [$this, 'getCsrf']),
        );
    }

    public function getLinkAttributes($method, $confirm = 'Are you sure?')
    {
        $html = sprintf('data-method="%s"', $method);

        if ($confirm !== false) {
            $html .= sprintf(' data-confirm="%s"', $confirm);
        } else {
            $html .= ' data-no-confirm';
        }

        return sprintf('%s data-csrf-token="%s"', $html, $this->getCsrf());
    }

    public function getCsrf()
    {
        return $this->csrfTokenManager->getToken($this->csrfTokenId);
    }
}
