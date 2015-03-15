<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace LazerBall\HitTracker\CommonBundle\Validator\Constraints;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueCollectionFieldValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private $collectionValues = [];

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $value = $propertyAccessor->getValue($value,
            $constraint->propertyPath
        );

        if (!empty($value) && in_array($value, $this->collectionValues)) {
            $stringValue = $this->getStringValue($value);

            $this->context->addViolationAt(
                $constraint->propertyPath,
                $constraint->message,
                ['{{ value }}' => $stringValue]
            );
        }
        $this->collectionValues[] = $value;
    }

    private function getStringValue($value)
    {
        if (is_object($value)) {
            $value = array_reverse(
                explode('\\', get_class($value))
            )[0];
        }

        return $value;
    }
}
