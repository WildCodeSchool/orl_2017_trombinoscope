<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 06/06/17
 * Time: 15:23
 */

namespace TrombiBundle\Twig;


class TwigExtension extends \Twig_Extension
{

    public function getName()
    {
        return 'twig_extension';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('basename', [$this, 'basenameFilter'])
        ];
    }

    /**
     * @var string $value
     * @return string
     */
    public function basenameFilter($value, $suffix = '')
    {
        return basename($value, $suffix);
    }
}