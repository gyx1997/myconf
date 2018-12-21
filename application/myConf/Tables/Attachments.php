<?php

    namespace myConf\Tables;

    /**
     * Class Attachments
     * @package myConf\Tables
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     */
    class Attachments extends \myConf\BaseTable {

        /**
         * @var array 当前字段的表名
         */
        private static $fields = array('attachment_id', 'attachment_file_size', 'attachment_is_image', 'attachment_file_name', 'attachment_image_width', 'attachment_image_height', 'attachment_download_times', 'attachment_original_name', 'attachment_tag_id', 'attachment_upload_time', 'attachment_tag_type', 'attachment_used');

        /**
         * Attachments constructor.
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回当前表的主键
         * @return string
         */
        public static function primary_key() : string {
            return 'attachment_id';
        }

        /**
         * 返回当前表名
         * @return string
         */
        public static function table() : string {
            return self::make_table('attachments');
        }

        /**
         * 返回当前表的所有字段
         * @return array
         */
        public static function fields() : array {
            return self::$fields;
        }
    }