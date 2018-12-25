<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/23
     * Time: 10:47
     */

    namespace myConf;

    use \myConf\Libraries\DbHelper;

    abstract class BaseMultiRelationTable extends BaseTable {

        /**
         * BaseMultiRelationTable constructor.
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 联系表的(逻辑)主键，是一个数组，表示数据库表的列名
         * @return array
         */
        public abstract function primary_key() : array;

        /**
         * 得到当前表的（实际）主键
         * @return string
         */
        protected abstract function _actual_pk() : string;

        /**
         * 表名
         * @return string
         */
        public abstract function table() : string;

        /**
         * 返回当前表的主键缓存名
         * @param $val
         * @return string
         */
        public function pk_cache_name($val) : string {
            //先排序
            ksort($val);
            return '<' . implode(',', array_keys($val)) . '>[' . implode(',', array_values($val)) . ']';
        }

        /**
         * 根据联合主键的值从表中取出一条数据
         * @param array $pk_val
         * @param bool $from_db
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function get($pk_val, $from_db = false) : array {
            $this->_check_primary_key($pk_val);
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
                $data = DbHelper::fetch_first($this->table(), $pk_val);
                //如果这张表启用了基于主键的缓存，那么将其写入缓存。
                $this->pk_cache_enabled() && $this->Cache->set($cache_key, $data);
            }
            return $data;
        }

        /**
         * 设置指定的键值
         * @param mixed $pk_val
         * @param array $data
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function set($pk_val, array $data = array()) : void {
            $this->_check_primary_key($pk_val);
            DbHelper::update($this->table(), $data, [$this->_actual_pk() => $this->_actual_pk_val($pk_val)]);
            $this->pk_cache_delete($pk_val);
        }

        /**
         * 判断指定主键值的记录是否存在
         * @param array $pk_val
         * @return bool
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function exist($pk_val) : bool {
            $this->_check_primary_key($pk_val);
            return DbHelper::exist_using_where($this->table(), $pk_val);
        }

        /**
         * 插入数据
         * @param array $data
         * @return int
         */
        public function insert(array $data = array()) : int {
            DbHelper::insert($this->table(), $data);
            return DbHelper::last_insert_id();
        }

        /**
         * 删除一条记录
         * @param array $pk_val
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function delete($pk_val) : void {
            $this->_check_primary_key($pk_val);
            DbHelper::delete($this->table(), [$this->_actual_pk() => $this->_actual_pk_val($pk_val)]);
            //从缓存中删除脏数据
            $this->pk_cache_delete($pk_val);
        }

        /**
         * @param array $pk_val
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        private function _check_primary_key(array &$pk_val) : void {
            $keys_in_val = array_keys($pk_val);
            $success = empty(array_diff($keys_in_val, $this->primary_key())) && empty(array_diff($this->primary_key(), $keys_in_val));
            if ($success === false) {
                throw new \myConf\Exceptions\DbCompositeKeysException('DB_PK_ERROR', 'Columns are out of primary key array given, or there are primary key columns which are not given.');
            }
        }

        /**
         * @param mixed $pk_val
         * @return int
         */
        protected function _actual_pk_val($pk_val) : int {
            return DbHelper::fetch_first($this->table(), $pk_val)[$this->_actual_pk()];
        }
    }