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

namespace LazerBall\HitTracker\Form\Type;

use LazerBall\HitTracker\FileUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericFileType extends AbstractType
{
    private $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modelTransformer = new class() implements DataTransformerInterface {
            public function transform($value): array
            {
                return ['file_name' => $value, 'file' => null];
            }

            public function reverseTransform($value): ?string
            {
                return $value['file_name'];
            }
        };

        $builder
            ->add('file_name', HiddenType::class)
            ->add('file', FileType::class, [
                  'label' => false,
            ])
            ->addModelTransformer($modelTransformer)
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'fileUploadListener'])
        ;
    }

    public function fileUploadListener(FormEvent $event)
    {
        $options = $event->getForm()->getConfig()->getOptions();
        $fileUploader = $this->fileUploader;
        $prefix = $fileUploader->cleanPrefix($options['upload_uri_prefix']);
        $data = $event->getData();

        if ($data['file'] instanceof UploadedFile) {
            $originalName = $data['file']->getClientOriginalName();
            if ($options['upload_delete_previous_file']) {
                $oldFilePath = $fileUploader->getUploadBaseDir() . $data['file_name'];
                if (is_file($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            if ($options['upload_use_provided_file_name']) {
                $fileName = $fileUploader->uploadFileWithName($data['file'], $originalName, $prefix);
            } else {
                $fileName = $fileUploader->upload($data['file'], $prefix);
            }
            $data['file_name'] = $fileName;
        }
        $event->setData($data);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $fileName = $form->get('file_name')->getData();
        $view->vars['upload_file_uri'] = $fileName;

        $view->children['file']->vars = array_replace($view->vars, $view->children['file']->vars);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => true,
            'upload_use_provided_file_name' => false,
            'upload_delete_previous_file' => true,
            'upload_uri_prefix' => '',
        ]);
    }
}
