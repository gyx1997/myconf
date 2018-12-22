<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 14:21
     */

    namespace myConf;

    /**
     * Class BaseTable 所有表的基类
     * @package myConf
     * @author _g63 <522975334@qq.com>
     * @version 2019.1
     * @property-read \myConf\Cache Cache
     */
    class BaseTable {
        /**
         * @var \CI_DB_active_record CI数据库接口
         */
        protected $db;
        /**
         * @var string 数据表前缀
         */
        private static $_table_prefix = 'myconf';

        /**
         * @var bool 是否适用主键缓存
         */
        protected $_use_pk_cache = true;

        /**
         * @var \myConf\Cache 缓存类
         */
        protected $_cache;

        /**
         * BaseTable constructor.
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function __construct() {
            //初始化CI数据库驱动
            $CI = &get_instance();
            $this->db = $CI->db;
            //初始化缓存驱动器
            $full_class = get_called_class();
            $classes = explode('\\', get_called_class());
            $full_class !== 'myConf\BaseTable' && $this->_cache = new Cache(str_replace('\\', '-', end($classes)));
        }

        /**
         * 魔术方法，读取缓存驱动器
         * @param $key
         * @return \myConf\Cache
         */
        public function __get($key) {
            return $this->_cache;
        }

        /**
         * 得到当前表的主键名
         * dummy for base class, should be override in derived classes.
         * @return string
         */
        public function primary_key() : string {
            return '';  //dummy for base class
        }

        /**
         * 得到当前表的包含前缀的表名
         * dummy for base class, should be override in derived classes.
         * @return string
         */
        public function table() : string {
            return '';  //dummy for base class, should be override in derived classes.
        }

        /**
         * 根据指定的Where条件组合判断数据表的记录是否存在
         * @param array $where_segment_array
         * @return bool 返回这样的记录是否存在
         */
        public function exist_using_where(array $where_segment_array) : bool {
            foreach ($where_segment_array as $field => $value) {
                $this->db->where($field, $value);
            }
            $this->db->select('COUNT(*)');
            $query = $this->db->get($this->table());
            $result = $query->row_array();
            return intval($result['COUNT(*)']) === 1;
        }

        /**
         * 得到指定表的带有前缀的表名
         * @param string $table_name
         * @return string
         */
        public static function make_table(string $table_name) : string {
            return self::$_table_prefix . '_' . $table_name;
        }

        /**
         * 从数据表中获取全部数据
         * @param array $where_segment_array
         * @param string $order_field
         * @param string $order_direction
         * @param int $start
         * @param int $limit
         * @return array
         */
        public function fetch_all(array $where_segment_array = array(), string $order_field = '', string $order_direction = '', int $start = 0, int $limit = 0) : array {
            $this->_pack_query_args($where_segment_array, $order_field, $order_direction, $start, $limit);
            $query_result = $this->db->get($this->table());
            if (empty($query_result->result_array())) {
                return array();
            }
            return $query_result->result_array();
        }

        /**
         * 包装查询参数
         * @param array $where_segment_array
         * @param string $order_field
         * @param string $order_direction
         * @param int $start
         * @param int $limit
         */
        private function _pack_query_args(array $where_segment_array = array(), string $order_field = '', string $order_direction = '', int $start = 0, int $limit = 0) : void {
            foreach ($where_segment_array as $field => $value) {
                $this->db->where($field, $value);
            }
            $order_field !== '' && $order_direction !== '' && $this->db->order_by($order_field, $order_direction);
            $limit !== 0 && $this->db->limit($limit, $start);
            return;
        }

        /**
         * 从数据库中取一条数据
         * @param array $where_segment_array
         * @param string $order_field
         * @param string $order_direction
         * @return array
         */
        public function fetch_first(array $where_segment_array = array(), string $order_field = '', string $order_direction = '') : array {
            $this->_pack_query_args($where_segment_array, $order_field, $order_direction, 0, 1);
            $query_result = $this->db->get($this->table());
            if (empty($query_result->row_array())) {
                return array();
            }
            return $query_result->row_array();
        }

        /**
         * 使用原始的数据库查询语句查询并取所有记录。
         * @param string $query_str
         * @param array $parameters
         * @return array
         */
        public function fetch_all_raw(string $query_str, array $parameters = array()) : array {
            $qr = $this->db->query($query_str, $parameters);
            if (empty($qr->result_array())) {
                return array();
            }
            return $qr->result_array();
        }

        /**
         * 返回指定SQL查询的第一条记录
         * @param       $query_str
         * @param array $parameters
         * @return array
         */
        protected function fetch_first_raw($query_str, $parameters = array()) : array {
            $qr = $this->db->query($query_str, $parameters);
            if (empty($qr->row_array())) {
                return array();
            }
            return $qr->row_array();
        }

        /**
         * @param string $pk_val
         * @param bool $from_db
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function get(string $pk_val, bool $from_db = false) : array {
            $data = array();
            //如果没有启用主键缓存，那么无论如何都是从数据库读取的。
            $from_db = !$this->_use_pk_cache || $from_db;
            $cache_key = $this->primary_key() . '[' . $pk_val . ']';
            if ($from_db === false) {
                try {
                    $data = $this->Cache->get($cache_key);
                } catch (\myConf\Exceptions\CacheMissException $e) {
                    $from_db = true;
                }
            }
            if ($from_db === true) {
                $this->db->where($this->primary_key(), $pk_val);
                $query_result = $this->db->get($this->table(), 1);
                $data = $query_result->row_array();
                if (empty($data)) {
                    $data = array();
                }
                //如果这张表启用了基于主键的缓存，那么将其写入缓存。
                $this->_use_pk_cache && $this->Cache->set($cache_key, $data);
            }
            return $data;
        }

        /**
         * 判断当前主键的记录是否存在
         * @param string $pk_val
         * @return bool
         */
        public function exist(string $pk_val) : bool {
            $this->db->where($this->primary_key(), $pk_val);
            $this->db->select('COUNT(1)');
            $query_result = $this->db->get($this->table(), 1);
            return intval($query_result->row_array()['COUNT(1)']) !== 0;
        }

        /**
         * 根据主键执行update操作
         * @param string $pk_val
         * @param array $data
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function set(string $pk_val, array $data = array()) : void {
            $this->db->where($this->primary_key(), $pk_val);
            $this->db->update($this->table(), $data);
            $this->pk_cache_delete($pk_val);
        }

        /**
         * 插入一条数据
         * @param array $data
         * @return int 返回当前自增键的最新一条记录的PK id
         */
        public function insert(array $data = array()) : int {
            $this->db->insert($this->table(), $data);
            return $this->db->insert_id();
        }

        /**
         * 根据主键删除一条记录
         * @param string $pk_val
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function delete(string $pk_val) : void {
            $this->pk_cache_delete($pk_val);
            $this->db->where($this->primary_key(), $pk_val);
            $this->db->delete($this->table());
        }

        /**
         * 对指定主键值确定的记录的某个字段做自增。
         * @param string $pk_val 主键键值
         * @param string $field 需要自增的字段
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function self_increase(string $pk_val, string $field) : void {
            $this->db->query('UPDATE ' . $this->table() . " SET $field=$field+1 WHERE " . $this->primary_key() . '=\'' . $pk_val . '\'');
            $this->pk_cache_delete($pk_val);
        }
        
        /**
         * 对指定主键值确定的记录的某个字段做自减。
         * @param string $pk_val 主键键值
         * @param string $field 需要自减的字段
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function self_decrease(string $pk_val, string $field) : void {
            $this->db->query('UPDATE ' . $this->table() . " SET $field=$field-1 WHERE " . $this->primary_key() . '=\'' . $pk_val . '\'');
            $this->pk_cache_delete($pk_val);
        }

        /**
         * 执行原生的SQL查询
         * @param string $sql
         * @param array $parameters
         */
        public function query(string $sql, array $parameters = array()) : void {
            $this->db->query($sql, $parameters);
        }

        /**
         * 获取根据主键的缓存名
         * @param string $pk_val
         * @return string
         */
        public function pk_cache_name(string $pk_val) : string {
            return $this->primary_key() . '[' . $pk_val . ']';
        }

        /**
         * 删除指定主键的缓存
         * @param string $pk_val
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function pk_cache_delete(string $pk_val) : void {
            //如果这张表启用了主键缓存，删掉它（因为记录不存在了）
            $this->_use_pk_cache && $this->Cache->delete($this->pk_cache_name($pk_val));
        }
    }