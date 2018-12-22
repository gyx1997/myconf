<?php


namespace myConf;

/**
 * Class BaseModel
 * @package myConf
 * @author _g63<522975334@qq.com>
 * @version 2019.1
 * @property-read \myConf\Tables $Tables
 */
class BaseModel
{
    /**
     * @var \myConf\Tables 数据表操作对象实例
     */
    private $_data_table;

    /**
     * myConf_BaseModel constructor.
     */
    public function __construct() {
        $this->_data_table = new Tables();
    }

    /**
     * 魔术方法，获取模型的缓存管理器和数据表管理器
     * @param $key
     * @return \myConf\Tables|null
     */
    public function __get($key) {
        if ($key === 'Tables') {
            return $this->_data_table;
        } else {
            return null;
        }
    }
}