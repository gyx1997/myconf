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

        /**
         * 获取一个会议的条目的所有id
         * @param int $conference_id
         * @param bool $force_read_db
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function get_ids_by_conference(int $conference_id, bool $force_read_db = false) : array {
            $data = array();
            $cache_key = 'Cats-Conf[' . strval($conference_id) . ']';
            if ($force_read_db === false) {
                try {
                    $data = $this->Cache->get($cache_key);
                } catch (\myConf\Exceptions\CacheMissException $e) {
                    $force_read_db = true;
                }
            }
            if ($force_read_db === true) {
                $data = $this->fetch_all_raw("SELECT category_id FROM " . $this->table() . " WHERE conference_id = $conference_id ORDER BY category_display_order ASC");
                foreach ($data as &$val) {
                    $val = $val['category_id'];
                }
                $this->Cache->set($cache_key, $data);
            }
            return $data;
        }

        /**
         * 删除Conference-Categories联系的缓存
         * @param int $conference_id
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function delete_conference_categories_cache(int $conference_id) : void {
            $cache_key = 'Cats-Conf[' . strval($conference_id) . ']';
            $this->Cache->delete($cache_key);
            return;
        }
    }