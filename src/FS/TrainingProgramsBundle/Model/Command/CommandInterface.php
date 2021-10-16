<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 07.10.2016
 * Time: 14:57
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command;

/**
 * Interface CommandInterface
 * @package FS\TrainingProgramsBundle\Model\Command
 */
interface CommandInterface
{
    /**
     * @return mixed
     */
    public function execute();
}