<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 24.10.2016
 * Time: 12:14
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Validator\Constraints;


use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueInCollectionValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        // Apply the property path if specified
        if ($constraint->propertyPath) {
            $propertyAccessor = new PropertyAccessor();
            $value = $propertyAccessor->getValue($value, $constraint->propertyPath);
        }

        // Check that the value is not in the array
        if (in_array($value, $constraint->collectionValues)){
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();

            $this->context->addViolation($constraint->message, array());
        }

        // Add the value in the array for next items validation
        $constraint->collectionValues[] = $value;
    }
}