<?php

    namespace myConf\Tables;

    class Categories extends \myConf\BaseTable {

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
        public function primary_key() : string {
            return 'category_id';
        }

        /**
         * 得到当前表名
         * @return string
         */
        public function table() : string {
            return $this->make_table('categories');
        }

    }