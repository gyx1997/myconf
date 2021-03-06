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
    class Papers extends \myConf\BaseCompositeKeyTable {

        /**
         * Papers constructor.
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回主键
         * @return array
         */
        public function primary_key() : array {
            return ['paper_logic_id', 'paper_version'];
        }

        /**
         * 返回实际主键
         * @return string
         */
        protected function _actual_pk() : string {
            return 'paper_id';
        }

        /**
         * @return string
         */
        public function table() : string {
            return DbHelper::make_table('papers');
        }

        /**
         * 得到新的paper的逻辑id号（取自上一个物理ID号的下一位），需要确保不重复
         * @return int
         */
        public function get_new_paper_logic_id() : int {
            //注意MYSQL 8.0 需要 set global information_schema_stats_expiry=0
            ENVIRONMENT === 'development' && DbHelper::query('SET information_schema_stats_expiry=0');
            $table_raw = $this->table();
            $table = $this->table();
            //$table = substr($table_raw, 1, strlen($table_raw) - 2);
            return intval(DbHelper::fetch_first_raw("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 
'$table'")['AUTO_INCREMENT']);
        }
    }