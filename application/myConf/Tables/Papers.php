<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/24
     * Time: 13:46
     */

    namespace myConf\Tables;

    use myConf\Libraries\DbHelper;

    /**
     * Class Papers
     * @package myConf\Tables
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     */
    class Papers extends \myConf\BaseEntityTable {

        public function __construct() {
            parent::__construct();
        }

        /**
         * @return string
         */
        public function primary_key() {
            return 'paper_id';
        }

        protected function _actual_pk() : string {
            return 'paper_id';
        }

        /**
         * @return string
         */
        public function table() : string {
            return DbHelper::make_table('papers');
        }

    }