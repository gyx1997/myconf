<?php

    namespace myConf\Tables;

    use myConf\Libraries\DbHelper;

    /**
     * Class Attachments
     * @package myConf\Tables
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     */
    class Attachments extends \myConf\BaseEntityTable {

        public $tag_types = array('document' => 'document', 'paper' => 'paper', 'conf' => 'conf', '' => '');

        public const tag_type_conference = 'conf';
        public const tag_type_document = 'document';
        public const tag_type_paper = 'paper';
        public const tag_type_non_restrict = '';

        /**
         * Attachments constructor.
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回当前表的主键
         * @return string
         */
        public function primary_key() : string {
            return 'attachment_id';
        }

        /**
         * 返回当前表名
         * @return string
         */
        public function table() : string {
            return DbHelper::make_table('attachments');
        }
    }