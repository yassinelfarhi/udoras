<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 30.11.2016
 * Time: 16:55
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Twig\Extension;
use FS\TrainingProgramsBundle\Util\CheckFilterForNull;

/**
 * Class PdfFiltersExtension
 * @package FS\UdorasBundle\Twig\Extension
 * @author <vladislav@fora-soft.com>
 */
class PdfFiltersExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('spaces', [$this, 'addSpaces']),
            new \Twig_SimpleFilter('cut_long', [$this, 'cutLong']),
            new \Twig_SimpleFilter('filter_conditions', [$this, 'filterConditions']),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('check_array', [$this, 'checkArray'])
        ];
    }

    /**
     * @param $field
     * @return string
     */
    public function addSpaces($field)
    {
        if (strtolower($field) === 'datebetween') {
            return "date between";
        }
        if (strtolower($field) === 'expiresbetween') {
            return "Expires between";
        }
        if (strtolower($field) === 'pricebetween') {
            return "Price between";
        }
        if (strtolower($field) === 'totalbetween') {
            return "Total between";
        }
        return $field;
    }

    /**
     * @param $field
     * @return string
     */
    public function cutLong($field)
    {
        if (strlen($field) <= 25) {
            return $field;
        }

        $result = '';

        $len = strlen($field);
        $start = 0;
        $end = 25;
        do {
            $result .= substr($field, $start, 25) . "\n";
            $start = $end + 1;
            $end += 25;
            if ($end > $len) {
                $end = $len;
            }
        } while ($start <= $len);

        return $result;
    }

    /**
     * @param $field
     * @return bool
     */
    public function checkArray($field){
        foreach ($field as $item){
            if ($item !== null){
                return true;
            }
        }
        return false;
    }

    public function filterConditions($conditions)
    {
        return CheckFilterForNull::clear($conditions);
    }
}