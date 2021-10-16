<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 11.11.2016
 * Time: 16:03
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Twig\Extension;

use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;

/**
 * Class TrainingStatusExtension
 * @package FS\UdorasBundle\Twig\Extension
 * @author <vladislav@fora-soft.com>
 */
class TrainingStatusExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('training_status', [$this, 'statusFilter']),
            new \Twig_SimpleFilter('status_class', [$this, 'getClassByStatus'])
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_date_end', [$this, 'getDateEnd']),
            new \Twig_SimpleFunction('get_date_expires', [$this, 'getDateExpires'])
        ];
    }

    /**
     * @param $status
     * @return string
     */
    public function statusFilter($status)
    {
        if ($status === EmployeeTrainingState::FINAL_STATUS_PASSED) {
            return 'Passed';
        } elseif ($status === EmployeeTrainingState::FINAL_STATUS_FAILED) {
            return 'Failed';
        } else {
            return 'Pending';
        }
    }

    /**
     * @param $state
     * @return string
     */
    public function getClassByStatus($state)
    {
        if ($state === EmployeeTrainingState::FINAL_STATUS_PASSED) {
            return 'text-success';
        } elseif ($state === EmployeeTrainingState::FINAL_STATUS_FAILED) {
            return 'text-danger';
        } else {
            return 'text-warning';
        }
    }

    /**
     * @param EmployeeTrainingState $trainingState
     * @return string
     */
    public function getDateEnd(EmployeeTrainingState $trainingState)
    {
        if($trainingState->getPassedStatus() === EmployeeTrainingState::FINAL_STATUS_IN_PROGRESS){
            return '-';
        } else {
            return $trainingState->getEndTime()->format('d M. Y');
        }
    }
    /**
     * @param EmployeeTrainingState $trainingState
     * @return string
     */
    public function getDateExpires(EmployeeTrainingState $trainingState)
    {
        if(empty($trainingState->getValidUntil()) || $trainingState->getPassedStatus() !== EmployeeTrainingState::FINAL_STATUS_PASSED){
            return '-';
        } else {
            return $trainingState->getValidUntil()->format('d M. Y');
        }
    }

    public function getName()
    {
        return 'training_status_extension';
    }
}