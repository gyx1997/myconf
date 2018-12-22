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
     * Class Tables
     * @package myConf
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     * @property-read \myConf\Tables\Attachments Attachments
     * @property-read \myConf\Tables\Categories Categories
     * @property-read \myConf\Tables\ConferenceMembers ConferenceMembers
     * @property-read \myConf\Tables\Conferences Conferences
     * @property-read \myConf\Tables\Configs Configs
     * @property-read \myConf\Tables\Documents Documents
     * @property-read \myConf\Table\Scholars Scholars
     * @property-read \myConf\Tables\Users Users
     */
    class Tables {
        /**
         * @var array 加载了的表的集合
         */
        private $_tables = array();

        private $_table_for_raw_sql;

        public function __construct() {
            $this->_table_for_raw_sql = new BaseTable();
        }

        /**
         * 返回指定的表实例对象（类名大小写敏感）
         * @param string $table_name
         * @return BaseTable
         */
        public function __get(string $table_name) : \myConf\BaseTable {
            if (!isset($this->_tables[$table_name])) {
                $class_name = '\\myConf\\Tables\\' . $table_name;
                $this->_tables[$table_name] = new $class_name();
            }
            return $this->_tables[$table_name];
        }

        /**
         * 用SQL语句取第一条记录
         * @param string $sql
         * @param array $arguments
         * @return array
         */
        public function fetch_first(string $sql, array $arguments = array()) : array {
            return $this->_table_for_raw_sql->fetch_all_raw($sql, $arguments);
        }

        /**
         * 用SQL语句取全部记录
         * @param string $sql
         * @param array $arguments
         * @return array
         */
        public function fetch_all(string $sql, array $arguments = array()) : array {
            return $this->_table_for_raw_sql->fetch_all_raw($sql, $arguments);
        }

        /**
         * 执行SQL查询
         * @param string $sql
         * @param array $arguments
         */
        public function query(string $sql, array $arguments = array()) : void {

        }
    }