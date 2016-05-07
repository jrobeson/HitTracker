<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 */

namespace LazerBall\HitTracker\CommonBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration as RequestConfigurationBase;

class RequestConfiguration extends RequestConfigurationBase
{
    /**
     * @param $name
     *
     * @return null|string
     *
     * @todo find a fix for https://github.com/Sylius/Sylius/issues/4959
     *       so we don't have to override this class
     */
    public function getDefaultTemplate($name)
    {
        $templatesNamespace = $this->metadata->getTemplatesNamespace();
        if (0 === strpos($templatesNamespace, '@')) { // twig namespaced template
            $tpl = sprintf('%s/%s.%s', $this->metadata->getTemplatesNamespace(), $name, 'html.twig');
        } else {
            $tpl = sprintf('%s:%s.%s', $this->metadata->getTemplatesNamespace() ?: ':', $name, 'html.twig');
        }

        return $tpl;
    }
}
