<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 14:21
     */

    namespace myConf;

    use \myConf\Libraries\DbHelper;

    /**
     * Class BaseEntityTable 所有实体表的基类
     * @package myConf
     * @author _g63 <522975334@qq.com>
     * @version 2019.1
     * @property-read \myConf\Cache Cache
     */
    abstract class BaseEntityTable extends BaseTable {

        /**
         * BaseTable constructor.
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 得到当前表的主键名
         * dummy for base class, should be override in derived classes.
         * @return string
         */
        public abstract function primary_key();

        /**
         * 得到当前表的（实际）主键
         * @return string
         */
        protected abstract function _actual_pk() : string;

        /**
         * 得到当前表的包含前缀的表名
         * dummy for base class, should be override in derived classes.
         * @return string
         */
        public abstract function table() : string;

        /**
         * @param string $pk_val
         * @param bool $from_db
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function get($pk_val, bool $from_db = false) : array {
            $data = array();
            //如果没有启用主键缓存，那么无论如何都是从数据库读取的。
            $from_db = !$this->pk_cache_enabled() || $from_db;
            $cache_key = $this->pk_cache_name($pk_val);
            if ($from_db === false) {
                try {
                    $data = $this->Cache->get($cache_key);
                } catch (\myConf\Exceptions\CacheMissException $e) {
                    $from_db = true;
                }
            }
            if ($from_db === true) {
                $data = DbHelper::fetch_first_raw('SELECT * FROM ' . $this->table() . ' WHERE ' . $this->primary_key() . '=\'' . $pk_val . '\' LIMIT 1');
                //如果这张表启用了基于主键的缓存，那么将其写入缓存。
                $this->pk_cache_enabled() && $this->Cache->set($cache_key, $data);
            }
            return $data;
        }

        /**
         * 判断当前主键的记录是否存在
         * @param string $pk_val
         * @return bool
         */
        public function exist($pk_val) : bool {
            return DbHelper::exist_using_where($this->table(), array($this->primary_key() => $pk_val));
        }

        /**
         * 根据主键执行update操作
         * @param string $pk_val
         * @param array $data
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function set($pk_val, array $data = array()) : void {
            //对于实际主键id和逻辑主键id不一致的，先查询找出实际主键id
            DbHelper::update($this->table(), $data, [$this->_actual_pk() => $this->_actual_pk_val($pk_val)]);
            $this->pk_cache_delete($pk_val);
        }

        /**
         * 插入一条数据
         * @param array $data
         * @return int 返回当前自增键的最新一条记录的PK id（实体表应该对应有id）
         */
        public function insert(array $data = array()) : int {
            DbHelper::insert($this->table(), $data);
            return DbHelper::last_insert_id();
        }

        /**
         * 根据主键删除一条记录
         * @param string $pk_val
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function delete($pk_val) : void {
            DbHelper::query('DELETE FROM `' . $this->table() . '` WHERE ' . $this->_actual_pk() . '= \'' . $this->_actual_pk_val($pk_val) . '\'');
            $this->pk_cache_delete($pk_val);
        }

        /**
         * 对指定主键值确定的记录的某个字段做自增。
         * @param string $pk_val 主键键值
         * @param string $field 需要自增的字段
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function self_increase(string $pk_val, string $field) : void {
            DbHelper::query('UPDATE ' . $this->table() . " SET $field=$field+1 WHERE " . $this->_actual_pk() . '=\'' . $this->_actual_pk_val($pk_val) . '\'');
            $this->pk_cache_delete($pk_val);
        }

        /**
         * 对指定主键值确定的记录的某个字段做自减。
         * @param string $pk_val 主键键值
         * @param string $field 需要自减的字段
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function self_decrease(string $pk_val, string $field) : void {
            DbHelper::query('UPDATE ' . $this->table() . " SET $field=$field-1 WHERE " . $this->_actual_pk() . '=\'' . $this->_actual_pk_val($pk_val) . '\'');
            $this->pk_cache_delete($pk_val);
        }

        /**
         * 获取根据主键的缓存名
         * @param string $pk_val
         * @return string
         */
        public function pk_cache_name($pk_val) : string {
            return '<' . $this->primary_key() . '>[' . $pk_val . ']';
        }

        /**
         * @param $pk_val
         * @return mixed
         */
        protected function _actual_pk_val($pk_val) : int {
            return ($this->primary_key() !== $this->_actual_pk()) ? DbHelper::fetch_first_raw('SELECT ' . $this->_actual_pk() . ' FROM ' . $this->table() . ' WHERE ' . $this->primary_key() . '=\'' . $pk_val . '\' LIMIT 1')[$this->_actual_pk()] : $pk_val;
        }
    }