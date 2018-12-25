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
         * @return string
         */
        protected function _actual_pk() : string {
            return 'attachment_id';
        }

        /**
         * 返回当前表名
         * @return string
         */
        public function table() : string {
            return DbHelper::make_table('attachments');
        }

        /**
         * @param string $attachment_tag_type
         * @param int $attachment_tag_id
         * @return array
         */
        public function get_used(string $attachment_tag_type, int $attachment_tag_id) : array {
            return $this->fetch_all(array(
                    'attachment_tag_type' => $attachment_tag_type,
                    'attachment_tag_id' => $attachment_tag_id,
                    'attachment_used' => 1,
                ));
        }

        /**
         * 获取某一类的未使用的附件
         * @param string $tag_type
         * @param int $tag_id
         * @return array
         */
        public function get_unused(string $tag_type, int $tag_id) : array {
            return $this->fetch_all([
                    'attachment_tag_type' => $tag_type,
                    'attachment_tag_id' => $tag_id,
                    'attachment_used' => 0,
                ]);
        }

        /**
         * @param int $attachment_id
         * @param bool $used_status
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function set_used_status(int $attachment_id, bool $used_status = true) : void {
            $this->set(strval($attachment_id), array('attachment_used' => ($used_status ? '1' : '0')));
        }

    }