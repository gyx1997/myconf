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
 * @author _g63 <522975334@qq.com>
 * @version 2019.1
 * @property-read \myConf\Models Models
 */
class BaseService
{
    /**
     * @var Models 模型管理器
     */
    private $_models;

    /**
     * BaseService constructor.
     */
    public function __construct()
    {
        $this->_models = new \myConf\Models();
    }

    /**
     * 魔术方法，获取服务的模型
     * @param $key
     * @return \myConf\Models|null
     */
    public function __get($key) {
        if ($key === 'Models') {
            return $this->_models;
        }
        return null;
    }
}

