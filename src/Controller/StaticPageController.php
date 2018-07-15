<?php declare(strict_types=1);
/**
 * Copyright (C) 2017 Johnny Robeson <johnny@localmomentum.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StaticPageController extends AbstractController
{
    /** @var string */
    private $baseTemplate;
    /** @var string */
    private $wrapperTemplate;
    /** @var string */
    private $templateExtension;

    public function __construct()
    {
        $this->baseTemplate = 'base.html.twig';
        $this->templateExtension = 'html.twig';
        $this->wrapperTemplate = '@Pages/layout.html.twig';
    }

    protected function getTemplatePath(string $pageName, string $locale = 'en'): string
    {
        return sprintf('@Pages/%s/%s.%s', $locale, $pageName, $this->templateExtension);
    }

    public function showAction(Request $request, string $name): Response
    {
        $locale = $request->getLocale();
        $templatePath = $this->getTemplatePath($name, $locale);

        if (!$this->container->get('templating')->exists($templatePath)) {
            // try fetching the same template with en
            $templatePath = $this->getTemplatePath($name, 'en');
        }

        if (!$this->container->get('templating')->exists($templatePath)) {
            throw $this->createNotFoundException();
        }

        return $this->render($this->wrapperTemplate, [
                'baseTemplate' => $this->baseTemplate,
                'templatePath' => $templatePath,
                'pageName' => $name,
            ]
        );
    }
}
