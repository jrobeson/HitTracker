<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace HitTracker\CommonBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class UniqueCollectionField extends Constraint
{
    public $message = '{{ value }} cannot be used more than once';
    public $propertyPath;
}
