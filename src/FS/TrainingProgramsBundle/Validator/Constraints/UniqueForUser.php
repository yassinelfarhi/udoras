<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 24.10.2016
 * Time: 12:29
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueForUser
 * @package FS\TrainingProgramsBundle\Validator\Constraints
 * @author <vladislav@fora-soft.com
 *
 * @Annotation
 *
 */
class UniqueForUser extends Constraint
{
    public $message = 'Title must be unique for this customer';

    public $errorPath = null;
    
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