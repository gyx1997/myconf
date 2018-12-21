<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 17:05
 */

namespace myConf;

/**
 * Class BaseService
 * @package myConf
 */
class BaseService
{
    /**
     * @var ModelManager 模型管理器
     */
    private $_models;

    /**
     * BaseService constructor.
     */
    public function __construct()
    {
        $this->_models = new \myConf\ModelManager();
    }

    /**
     * 返回模型管理器
     * @return ModelManager
     */
    public function models(): \myConf\ModelManager
    {
        return $this->_models;
    }

}

