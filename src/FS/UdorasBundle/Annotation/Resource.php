<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 29.09.2016
 * Time: 11:18
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Class Resource
 * @package FS\UdorasBundle\Annotation
 * @author <vladislav@fora-soft.com>
 *
 * @Annotation
 */
class Resource
{
    /**
     * @var string
     * @Required
     */
    public $resource;

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }
}