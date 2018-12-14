<?php

class MyConfBaseClass
{
    public function __get($key)
    {
        var_dump($this->$key);
        return $this->$key;
    }
}