<?php

    namespace myConf\Tables;

    /**
     * Class Attachments
     * @package myConf\Tables
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     */
    class Attachments extends \myConf\BaseTable {

        public $tag_types = array('document' => 'document', 'paper' => 'paper', 'conf' => 'conf', '' => '');

        public const tag_type_conference = 'conf';
        public const tag_type_document = 'document';
        public const tag_type_paper = 'paper';
        public const tag_type_non_restrict = '';

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
        public function primary_key() : string {
            return 'attachment_id';
        }

        /**
         * 返回当前表名
         * @return string
         */
        public function table() : string {
            return $this->make_table('attachments');
        }

        public function get_list(string $tag_type = '', int $tag_id = 0, bool $image_only = false, int $start = 0, int $limit = 10) : array {
            $this->db->select('*');
            if ($tag_type !== '' && isset($this->tag_types[$tag_type])) {
                $this->db->where('attachment_tag_type', $this->tag_types[$tag_type]);
                if ($tag_id !== 0) {
                    $this->db->where('attachment_tag_id', $tag_id);
                }
            }
            $image_only === true && $this->db->where('attachment_is_image', 1);
            $this->db->limit($limit, strval($start));
            $this->db->order_by('attachment_id', 'DESC');
            $query_result = $this->db->get($this->table());
            return $query_result->result_array();
        }
    }