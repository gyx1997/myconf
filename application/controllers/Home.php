<?php

/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/11/12
 * Time: 16:47
 */
class Home extends CI_Controller
{
    public function index()
    {
        include APPPATH . 'myConf' . DIRECTORY_SEPARATOR . 'MainExecute.php';
    }
}