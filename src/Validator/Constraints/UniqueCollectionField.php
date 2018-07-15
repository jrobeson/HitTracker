<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class UniqueCollectionField extends Constraint
{
    /** @var string */
    public $message = '%value% cannot be used more than once';
    /** @var string */
    public $propertyPath;
}
