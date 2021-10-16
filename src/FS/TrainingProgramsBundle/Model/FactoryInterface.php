<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 07.10.2016
 * Time: 14:59
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model;

/**
 * Interface FactoryInterface
 * @package FS\TrainingProgramsBundle\Model
 */
interface FactoryInterface
{
    /**
     * @param $command
     * @param $object
     * @return mixed
     */
    public function factory($command, $object);
}