<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/21
     * Time: 16:36
     */

    namespace myConf;

    /**
     * 表管理器
     * Class DataTableManager
     * @package myConf
     */
    class DataTableManager {
        /**
         * @var array 加载了的表的集合
         */
        private $_tables = array();

        /**
         * 返回指定的表实例对象（类名大小写敏感）
         * @param string $table_name
         * @return BaseTable
         */
        public function __get(string $table_name) : \myConf\BaseTable {
            if (!isset($this->_services[$table_name])) {
                $class_name = '\\myConf\\Tables\\' . $table_name;
                $this->_tables[$table_name] = new $class_name();
            }
            return $this->_tables[$table_name];
        }
    }