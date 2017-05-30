<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 30/05/17
 * Time: 09:47
 */

namespace TrombiBundle\Entity;


class Wilder
{
    private $input;
    private $category;

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }


}