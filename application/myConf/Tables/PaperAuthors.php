<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/24
     * Time: 15:03
     */

    namespace myConf\Tables;

    use myConf\Libraries\DbHelper;

    /**
     * Class PaperAuthors
     * @package myConf\Tables
     * @author _g63
     * @version 2019.1
     */
    class PaperAuthors extends \myConf\BaseEntityTable {

        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回主键
         * @return string
         */
        public function primary_key() : string {
            return 'author_id';
        }

        /**
         * 返回实际主键
         * @return string
         */
        protected function _actual_pk() : string {
            return 'author_id';
        }

        /**
         * 返回包含前缀的数据表表名
         * @return string
         */
        public function table() : string {
            return DbHelper::make_table('paper_authors');
        }

    }