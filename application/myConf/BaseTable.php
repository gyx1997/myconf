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
     */
    abstract class BaseTable {
        /**
         * @var \CI_DB_active_record CI数据库接口
         */
        protected $db;
        /**
         * @var string 数据表前缀
         */
        private static $_table_prefix = 'myconf';

        /**
         * myConf BaseTable constructor.
         */
        public function __construct() {
            //初始化CI数据库驱动
            $CI = &get_instance();
            $this->db = $CI->db;
        }

        /**
         * 得到当前表的主键名
         * @return string
         */
        public static abstract function primary_key() : string;

        /**
         * 得到当前表的包含前缀的表名
         * @return string
         */
        public static abstract function table() : string;

        /**
         * 得到当前表的所有字段名
         * @return array
         */
        public static abstract function fields() : array;

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
            $query = $this->db->get(self::table());
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
            $query_result = $this->db->get(self::table());
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
         * 根据主键获取数据
         * @param string $pk_val
         * @return array
         */
        public function get(string $pk_val) : array {
            $this->db->where(self::primary_key(), $pk_val);
            $query_result = $this->db->get(self::table(), 1);
            if (empty($query_result->row_array())) {
                return array();
            }
            return $query_result->row_array();
        }

        /**
         * 判断当前主键的记录是否存在
         * @param string $pk_val
         * @return bool
         */
        public function exist(string $pk_val) : bool {
            $this->db->where(self::primary_key(), $pk_val);
            $this->db->select('COUNT(1)');
            $query_result = $this->db->get(self::table(), 1);
            return intval($query_result->row_array()['COUNT(1)']) !== 0;
        }

        /**
         * 执行update操作，根据主键
         * @param string $pk_val
         * @param array $data
         */
        public function set(string $pk_val, array $data = array()) : void {
            $this->db->where(self::primary_key(), $pk_val);
            $this->db->update(self::table(), $data);
        }

        /**
         * 插入一条数据
         * @param array $data
         * @return int 返回当前自增键的最新一条记录的PK id
         */
        public function insert(array $data = array()) : int {
            $this->db->insert(self::table(), $data);
            return $this->db->insert_id();
        }

        /**
         * 根据主键删除一条记录
         * @param string $pk_val 主键键值
         */
        public function delete(string $pk_val) : void {
            $this->db->where(self::primary_key(), $pk_val);
            $this->db->delete(self::table());
        }

        /**
         * 对指定主键值确定的记录的某个字段做自增。
         * @param string $pk_val 主键键值
         * @param string $field 需要自增的字段
         */
        public function self_increase(string $pk_val, string $field) : void {
            $this->db->query('UPDATE ' . self::table() . " SET $field=$field+1 WHERE " . self::primary_key() . '=\'' . $pk_val . '\'');
        }

        /**
         * 对指定主键值确定的记录的某个字段做自减。
         * @param string $pk_val 主键键值
         * @param string $field 需要自减的字段
         */
        public function self_decrease(string $pk_val, string $field) : void {
            $this->db->query('UPDATE ' . self::table() . " SET $field=$field-1 WHERE " . self::primary_key() . '=\'' . $pk_val . '\'');
        }

        /**
         * 将需要序列化字段的数据序列化并合并到主数据上
         * @param array $data
         * @param string $field
         * @param array $data_to_pack
         * @return array
         */
        public function pack_serialized_field(array $data, string $field, array $data_to_pack) : array {
            return array_merge($data, array($field => serialize($data_to_pack)));
        }

        /**
         * 从主数据中分离出序列化的数据
         * @param array $data
         * @param string $field
         * @return array
         */
        public function unpack_serialized_field(array $data, string $field) : array {
            if (!isset($data[$field])) {
                return $data;
            }
            $new_data = array();
            $unserialized_data = unserialize($data[$field]);
            foreach ($data as $key => $value) {
                if ($key !== $field) {
                    $new_data[$key] = $value;
                }
            }
            return array_merge($new_data, $unserialized_data);
        }
    }