<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 17:32
     */

    namespace myConf\Tables;

    class Scholars extends \myConf\BaseTable {

        private static $fields = array('scholar_id', 'scholar_email', 'scholar_first_name', 'scholar_last_name', 'scholar_chn_full_name', 'scholar_address', 'scholar_institution', 'scholar_department', 'scholar_prefix');

        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回当前的主键
         * @return string
         */
        public function primary_key() : string {
            return 'scholar_id';
        }

        /**
         * 返回当前的表名
         * @return string
         */
        public function table() : string {
            return $this->make_table('scholars');
        }

        /**
         * 返回当前所有字段
         * @return array
         */
        public function fields() : array {
            return self::$fields;
        }

        /**
         * 获取某一个Scholar的信息
         * @param $email
         * @return array
         */
        public function get_by_email(string $email) : array {
            return $this->fetch_first(array('scholar_email' => $email));
        }

        /**
         * 修改scholar信息
         * @param $email
         * @param $data
         * @deprecated
         */
        public function update_by_email(string $email, array $data) : void {
            $this->db->where('scholar_email', $email);
            $this->db->update($this->table(), $data);
        }

        /**
         * 更新scholar信息
         * @param string $email
         * @param array $data
         */
        public function set_by_email(string $email, array $data = array()) : void {
            $this->db->where('scholar_email', $email);
            $this->db->update($this->table(), $data);
            return;
        }

        /**
         * 判断scholar是否存在
         * @param string $email
         * @return bool
         */
        public function exist_by_email(string $email) : bool {
            return $this->exist_using_where(array('scholar_email' => $email));
        }
    }