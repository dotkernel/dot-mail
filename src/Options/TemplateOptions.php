<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace Dot\Mail\Options;

/**
 * Class TemplateOptions
 * @package Dot\Mail\Options
 */
class TemplateOptions
{
    /** @var array */
    protected $params = [];

    /** @var  string */
    protected $name;

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return TemplateOptions
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return TemplateOptions
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


}