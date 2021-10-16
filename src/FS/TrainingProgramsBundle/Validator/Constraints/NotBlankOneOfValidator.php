<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 24.10.2016
 * Time: 12:30
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Validator\Constraints;


use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotBlankOneOfValidator extends ConstraintValidator
{
    /**
     * @param TrainingProgram $object
     * @param NotBlankOneOf $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!empty($value)) {
            return;
        }
        $root = $this->context->getRoot();
        $otherValue = $root->get($constraint->otherField)->getData();
        if (!empty($otherValue)) {
            return;
        }
        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}