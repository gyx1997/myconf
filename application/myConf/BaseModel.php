<?php


namespace myConf;

class BaseModel
{
    /**
     * @var \myConf\DataCacheManager 缓存操作对象实例
     */
    private $_cache_manager;
    /**
     * @var \myConf\DataTableManager 数据表操作对象实例
     */
    private $_data_table;
    /**
     * myConf_BaseModel constructor.
     */
    public function __construct()
    {
        $this->_cache_manager = new DataCacheManager(get_called_class());
        $this->_data_table = new DataTableManager();
    }

    /**
     * 缓存操作对象
     * @return DataCacheManager
     */
    public function cache(): \myConf\DataCacheManager
    {
        return $this->_cache_manager;
    }

    /**
     * 数据表操作对象
     * @return \myConf\DataTableManager
     */
    public function table() : \myConf\DataTableManager {
        return $this->_data_table;
    }
}