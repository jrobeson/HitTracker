<?php
/**
 * This file was "borrowed" from KnpLabs/RadBundle
 * @license MIT
 * @see https://github.com/KnpLabs/KnpRadBundle/
 */

namespace HitTracker\CommonBundle\Twig;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

class LinkAttributesExtension extends \Twig_Extension
{
    private $csrfProvider;
    private $intention;

    public function __construct(CsrfProviderInterface $csrfProvider, $intention = 'link')
    {
        $this->csrfProvider = $csrfProvider;
        $this->intention    = $intention;
    }

    public function getName()
    {
        return 'link_attributes';
    }

    public function getFunctions()
    {
        return array(
            'link_attr' => new \Twig_Function_Method($this, 'getLinkAttributes', array('is_safe' => array('html'))),
            'link_csrf' => new \Twig_Function_Method($this, 'getCsrf'),
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
        return $this->csrfProvider->generateCsrfToken($this->intention);
    }
}
