<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/22
     * Time: 17:53
     */

    namespace myConf\Libraries;

    /**
     * Class DbHelper 数据库函数类
     * @package myConf\Libraries
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     */
    class DbHelper {
        /**
         * @var string 表前缀。
         */
        private static $_table_prefix = 'myconf_';

        /**
         * @var \CI_DB_active_record
         */
        private static $_db_object;

        public static function init() {
            $CI = &get_instance();
            self::$_db_object = $CI->db;
        }

        /**
         * 得到CI的数据库对象
         * @return \CI_DB_active_record
         */
        public static function db() {
            return self::$_db_object;
        }

        /**
         * 开始事务
         */
        public static function begin_trans() : void {
            self::$_db_object->trans_strict(false);
            self::$_db_object->trans_begin();
        }

        /**
         * 结束事务
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public static function end_trans() : void {
            if (self::$_db_object->trans_status() === true) {
                self::$_db_object->trans_commit();
                return;
            }
            self::$_db_object->trans_rollback();
            throw new \myConf\Exceptions\DbTransactionException('DB_TRANS_ERROR', 'Tables transaction exception. Closest SQL: ' . self::$_db_object->last_query() . '  .', 10002);
        }

        /**
         * @param string $table_name
         * @return string
         */
        public static function make_table(string $table_name) : string {
            return self::$_table_prefix . $table_name;
        }

        /**
         * @param string $sql
         * @param array $parameters
         * @return array
         */
        public static function fetch_all_raw(string $sql, array $parameters = array()) : array {
            $CI = &get_instance();
            $qr = self::$_db_object->query($sql, $parameters);
            if (empty($qr->result_array())) {
                return array();
            }
            return $qr->result_array();
        }

        /**
         * @param string $sql
         * @param array $parameters
         * @return array
         */
        public static function fetch_first_raw(string $sql, array $parameters = array()) : array {
            $CI = &get_instance();
            $qr = self::$_db_object->query($sql, $parameters);
            if (empty($qr->row_array())) {
                return array();
            }
            return $qr->row_array();
        }

        /**
         * @param string $sql
         * @param array $parameters
         */
        public static function query(string $sql, array $parameters = array()) : void {
            self::$_db_object->query($sql, $parameters);
        }

        /**
         * 从数据表中获取全部数据
         * @param string $table
         * @param array $where_segment_array
         * @param string $order_field
         * @param string $order_direction
         * @param int $start
         * @param int $limit
         * @return array
         */
        public static function fetch_all(string $table, array $where_segment_array = array(), string $order_field = '', string $order_direction = '', int $start = 0, int $limit = 0) : array {
            self::_pack_query_args($where_segment_array, $order_field, $order_direction, $start, $limit);
            $query_result = self::$_db_object->get($table);
            if (empty($query_result->result_array())) {
                return array();
            }
            return $query_result->result_array();
        }

        /**
         * 从数据库中取一条数据
         * @param string $table
         * @param array $where_segment_array
         * @param string $order_field
         * @param string $order_direction
         * @return array
         */
        public static function fetch_first(string $table, array $where_segment_array = array(), string $order_field = '', string $order_direction = '') : array {
            self::_pack_query_args($where_segment_array, $order_field, $order_direction, 0, 1);
            $query_result = self::$_db_object->get($table);
            if (empty($query_result->row_array())) {
                return array();
            }
            return $query_result->row_array();
        }

        /**
         * 从数据表中删除一条数据
         * @param string $table
         * @param array $where_segment_array
         */
        public static function delete(string $table, array $where_segment_array) : void {
            foreach ($where_segment_array as $key => $value) {
                self::$_db_object->where($key, $value);
            }
            self::$_db_object->delete($table);
        }

        /**
         * 得到上次插入的自增字段的id值
         * @return int
         */
        public static function last_insert_id() : int {
            return self::$_db_object->insert_id();
        }

        /**
         * @param string $table
         * @param array $data
         * @param array $where_segment_array
         */
        public static function update(string $table, array $data, array $where_segment_array) : void {
            foreach ($where_segment_array as $key => $value) {
                self::$_db_object->where($key, $value);
            }
            self::$_db_object->update($table, $data);
        }

        /**
         * @param string $table
         * @param array $data
         */
        public static function insert(string $table, array $data) : void {
            self::$_db_object->insert($table, $data);
        }

        /**
         * @param string $table
         * @param array $data
         */
        public static function insert_array(string $table, array $data) : void {
            self::$_db_object->insert_batch($table, $data);
        }

        /**
         * @param string $table
         * @param array $where_segment_array
         * @return bool
         */
        public static function exist_using_where(string $table, array $where_segment_array) : bool {
            foreach ($where_segment_array as $field => $value) {
                self::$_db_object->where($field, $value);
            }
            self::$_db_object->select('COUNT(1)');
            $query = self::$_db_object->get($table);
            return intval($query->row_array()['COUNT(1)']) === 1;
        }

        /**
         * 包装查询参数
         * @param array $where_segment_array
         * @param string $order_field
         * @param string $order_direction
         * @param int $start
         * @param int $limit
         */
        private static function _pack_query_args(array $where_segment_array = array(), string $order_field = '', string $order_direction = '', int $start = 0, int $limit = 0) : void {
            foreach ($where_segment_array as $field => $value) {
                self::$_db_object->where($field, $value);
            }
            $order_field !== '' && $order_direction !== '' && self::$_db_object->order_by($order_field, $order_direction);
            $limit !== 0 && self::$_db_object->limit($limit, $start);
            return;
        }
    }