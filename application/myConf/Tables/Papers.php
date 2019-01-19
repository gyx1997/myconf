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
            return intval(DbHelper::fetch_first_raw("SELECT MAX(paper_id) AS maxId FROM {$this->table()}")['maxId']) + 1;
        }
    }