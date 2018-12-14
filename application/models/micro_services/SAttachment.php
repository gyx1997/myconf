<?php

/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/13
 * Time: 22:50
 */
class sAttachment extends CF_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mAttachment');
    }

    public function t()
    {
        echo('<script>alert("hello!");</script>');
    }
}