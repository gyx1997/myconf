<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2019/2/24
     * Time: 9:59
     */

    namespace myConf\Tables;

    /**
     * Class PaperReview
     * @package myConf\Tables
     * @author _g63<522975334@qq.com>
     * @version 2019.3
     */
    class PaperReview extends \myConf\BaseMultiRelationTable {
        /**
         * PaperReview constructor.
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function __construct() {
            parent::__construct();
        }

        public function primary_key() : array {
            return ['paper_id', 'paper_version', 'reviewer_email'];
        }

        /**
         * @return string
         */
        public function _actual_pk() : string {
            return 'review_id';
        }

        /**
         * @return string
         */
        public function table() : string {
            return \myConf\Libraries\DbHelper::make_table('paper_review');
        }
    }