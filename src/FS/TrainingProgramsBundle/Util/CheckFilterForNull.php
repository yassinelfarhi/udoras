<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 01.12.2016
 * Time: 10:39
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Util;

/**
 * Class CheckFilterForNull
 * @package FS\TrainingProgramsBundle\Util
 * @author <vladislav@fora-soft.com>
 */
class CheckFilterForNull
{
    /**
     * @param array $filter
     * @return bool
     */
    public static function check($filter)
    {
        foreach ($filter as $data) {
            if (is_array($data)) {
                /** @var  array $data */
                foreach ($data as $sub_data) {
                    if ($sub_data != null) {
                        return true;
                    }
                }
            } else if ($data != null) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $filter
     * @return array
     */
    public static function clear($filter){
        $result  = [];
        foreach ($filter as $key=>$data) {
            if (is_array($data)) {
               if(self::check($data)){
                   $result[$key] = $data;
               }
            } else if ($data != null) {
                $result[$key] = $data;
            }

        }
        return $result;
    }
}