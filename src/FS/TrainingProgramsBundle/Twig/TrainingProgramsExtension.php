<?php

namespace FS\TrainingProgramsBundle\Twig;


use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;

class TrainingProgramsExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('minutes_seconds', [$this, 'timeFilter']),
            new \Twig_SimpleFilter('training_name_format', [$this, 'cutTrainingProgramNameHTMLTag']),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_class_by_state', [$this, 'getClassByState'])
        ];
    }

    /**
     * @param $seconds
     * @return string
     */
    public function timeFilter($seconds)
    {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        if ($seconds == 0) {
            $seconds = '00';
        } elseif ($seconds < 10) {
            $seconds = '0' . $seconds;
        }

        return $minutes . ':' . $seconds;
    }

    public function getClassByState($state)
    {
        if ($state === EmployeeTrainingState::FINAL_STATUS_PASSED) {
            return 'text-success';
        } elseif ($state === EmployeeTrainingState::FINAL_STATUS_FAILED) {
            return 'text-danger';
        }

        return '';
    }

    public function cutTrainingProgramNameHTMLTag($name)
    {
        if(strlen($name) <= TrainingProgram::MAX_NAME_STRING_LEN){
            return $name;
        }

        $result = '';

        $nameArray = explode(' ', $name);
        if(count($nameArray) === 1){
            $len = strlen($name);
            $start = 0;
            $end = TrainingProgram::MAX_NAME_STRING_LEN;
            do {
                $result .= substr($name, $start, TrainingProgram::MAX_NAME_STRING_LEN) . "\n";
                $start = $end + 1;
                $end += TrainingProgram::MAX_NAME_STRING_LEN;
                if($end > $len){
                    $end = $len;
                }
            } while($start <= $len);
        } else {
            return $name;
            /*foreach ($nameArray as $item){
                $result .= $item . ' ';
                if(strlen($result) >= TrainingProgram::MAX_NAME_STRING_LEN){
                   $result .= "\n";
                }
            }*/
        }

        return $result;
    }


    public function getName()
    {
        return 'training_programs_extension';
    }
}