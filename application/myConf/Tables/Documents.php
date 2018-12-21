<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 23:40
     */

    namespace myConf\Tables;

    class Documents extends \myConf\BaseTable {

        private static $fields = array('document_id', 'document_category_id', 'document_display_order', 'document_title', 'document_html',);

        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回当前数据表名
         * @return string
         */
        public static function table() : string {
            return self::make_table('documents');
        }

        /**
         * 返回当前的主键名
         * @return string
         */
        public static function primary_key() : string {
            return 'document_id';
        }

        /**
         * 返回当前的字段名列表
         * @return array
         */
        public static function fields() : array {
            return self::$fields;
        }

        /**
         * @param int $category_id
         * @return array
         */
        public function get_documents_in_category(int $category_id) : array {
            $this->db->where('document_category_id', $category_id);
            $this->db->select('*');
            $query = $this->db->get(self::table());
            if (empty($query->result_array())) {
                return array();
            }
            return $query->result_array();
        }
    }