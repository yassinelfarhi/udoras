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

class UniqueForUserValidator extends ConstraintValidator
{

    /**
     * @param TrainingProgram $object
     * @param Constraint $constraint
     */
    public function validate($object, Constraint $constraint)
    {
        $title = $object->getTitle();
        $id = $object->getId();
        $customer = $object->getCustomer();

        $res = $customer->getTrainingPrograms()
            ->filter(function (TrainingProgram $tp) use ($title, $id) {
                return (
                    strtolower($tp->getTitle()) == strtolower($title) &&
                    $id != $tp->getId()
                );
            });

        if ($res->count() > 0) {
            if ($constraint->errorPath) {
                $this->context->buildViolation($constraint->message)
                    ->atPath($constraint->errorPath)
                    ->addViolation();
            } else {
                $this->context->buildViolation($constraint->message)
                    ->atPath($constraint->errorPath)
                    ->addViolation();
            }
        }
    }
}