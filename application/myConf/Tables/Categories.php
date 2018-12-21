<?php

    namespace myConf\Tables;

    class Categories extends \myConf\BaseTable {

        /**
         * @var array 表的字段列表
         */
        private static $fields = array('category_id', 'category_display_order', 'category_type', 'category_title', 'conference_id',);

        /**
         * Categories constructor.
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 得到当前表的主键
         * @return string
         */
        public static function primary_key() : string {
            return 'category_id';
        }

        /**
         * 得到当前表名
         * @return string
         */
        public static function table() : string {
            return self::make_table('categories');
        }

        /**
         * 得到当前表的所有字段
         * @return array
         */
        public static function fields() : array {
            return self::$fields;
        }
    }