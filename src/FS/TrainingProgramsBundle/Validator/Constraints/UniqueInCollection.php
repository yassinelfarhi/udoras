<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 24.10.2016
 * Time: 12:13
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueInCollection
 * @package FS\TrainingProgramsBundle\Validator\Constraints
 * @author <vladislav@fora-soft.com>\
 *
 * @Annotation
 */
class UniqueInCollection extends Constraint
{
    public $message = 'The error message (with %parameters%)';

    // The property path used to check wether objects are equal
    // If none is specified, it will check that objects are equal
    public $propertyPath = null;

    /**
     * @var array
     */
    public $collectionValues = [];

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}