<?php
/**
 * Created by PhpStorm.
 * User: pabloid
 * Date: 22.05.17
 * Time: 16:54
 */

namespace FS\TrainingProgramsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotBlankOneOf extends Constraint
{
    public $message = 'One of these fields should not be empty';
    public $otherField;

    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }

    /**
     * Get class constraints and properties
     *
     * @return array
     */
    public function getTargets()
    {
        return array(self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT);
    }
}