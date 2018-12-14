<?php

class MyConfBaseClass
{
    public function __get($key)
    {
        return $this->$key;
    }
}