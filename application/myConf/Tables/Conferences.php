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

        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回当前表的主键
         * @return string
         */
        public function primary_key() : string {
            return 'conference_id';
        }

        /**
         * 返回当前表名
         * @return string
         */
        public function table() : string {
            return $this->make_table('conferences');
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
            return $result_array;
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