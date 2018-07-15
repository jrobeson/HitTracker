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

namespace App\GameBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class DtoFormFactory
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function create(RequestConfiguration $requestConfiguration, object $dto): FormInterface
    {
        $formType = $requestConfiguration->getFormType();
        $formOptions = $requestConfiguration->getFormOptions();

        if ($requestConfiguration->isHtmlRequest()) {
            return $this->formFactory->create((string) $formType, $dto, $formOptions);
        }

        return $this->formFactory->createNamed('', (string) $formType, $dto, array_merge($formOptions, ['csrf_protection' => false]));
    }
}
