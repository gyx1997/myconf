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
 * @property-read \myConf\Callbacks Callbacks
 */
class BaseService
{
    /**
     * @var Models 模型管理器
     */
    private $_models;

    private $_callbacks;

    /**
     * BaseService constructor.
     */
    public function __construct()
    {
        $this->_models = new Models();
        $this->_callbacks = Callbacks::get_instance();
    }

    /**
     * 魔术方法，获取服务的模型
     * @param $key
     * @return \myConf\Callbacks|\myConf\Models|null
     */
    public function __get($key) {
        if ($key === 'Models') {
            return $this->_models;
        } else if ($key === 'Callbacks') {
            return $this->_callbacks;
        }
        return null;
    }
}

