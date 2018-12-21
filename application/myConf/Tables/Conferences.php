<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 22:57
     */

    namespace myConf\Tables;

    class Conferences extends \myConf\BaseTable {
        public static $conference_status = array('moderated' => 0, 'normal' => 1);

        private static $fields = array('conference_id', 'conference_status', 'conference_creator', 'conference_url', 'conference_name', 'conference_create_time', 'conference_start_time', 'conference_user_count', 'conference_extra');

        private static $fields_extra = array('use_paper_submission', 'paper_submission_end_time', 'host', 'qr_code', 'banner_image',);

        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回当前所有字段
         * @return array
         */
        public static function fields() : array {
            return self::$fields;
        }

        /**
         * 返回当前表的主键
         * @return string
         */
        public static function primary_key() : string {
            return 'conference_id';
        }

        /**
         * 返回当前表名
         * @return string
         */
        public static function table() : string {
            return self::make_table('conferences');
        }

        /**
         * 重写父类的get，获取会议信息。
         * @param string $pk_val
         * @return array
         */
        public function get(string $pk_val) : array {
            $result_array = parent::get($pk_val);
            return empty($result_array) ? array() : $this->unpack_serialized_field($result_array, 'conference_extra');
        }

        /**
         * 重写父类的set，更新会议信息。
         * @param string $pk_val
         * @param array $data
         */
        public function set(string $pk_val, array $data = array()) : void {
            //排除主键和序列化的键,因此要把其他键和值复制过来
            $basic_data = array_filter($data, function ($key) {
                return !($key === 'conference_id' && $key === 'conference_extra');
            }, ARRAY_FILTER_USE_KEY);
            //只选择需要序列化的键值对
            $new_data_extra = array();
            foreach (self::$fields_extra as $field) {
                isset($data[$field]) && $new_extra[$field] = $data[$field];
            }
            parent::set($pk_val, $this->pack_serialized_field($basic_data, 'conference_extra', $new_data_extra));
        }

        /**
         * 根据URL从数据库中取conference的信息
         * @param string $url
         * @return array
         */
        public function get_by_url(string $url) : array {
            $result_array = $this->fetch_first(array('conference_url' => $url));
            if (empty($result_array)) {
                return array();
            }
            return $this->unpack_serialized_field($result_array, 'conference_extra');
        }

        /**
         * 判断指定的URL的会议是否存在
         * @param string $url
         * @return bool
         */
        public function exist_by_url(string $url) : bool {
            return $this->exist_using_where(array('conference_url' => $url));
        }
    }