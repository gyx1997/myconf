<?php

namespace myConf\Models;


class Category extends \myConf\BaseModel
{
    /**
     * Category constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'categories';
        $this->_pk = 'category_id';
    }

    /**
     * @deprecated
     * @see \myConf\Models\Category::exist()
     * @param int $category_id
     * @return bool
     */
    public function has_category(int $category_id): bool
    {
        return $this->exist(strval($category_id));
    }

    /**
     * @param int $conference_id
     * @param string $category_name
     * @param string $category_type
     * @return int
     */
    public function add_category(int $conference_id, string $category_name, string $category_type): int
    {
        $this->db->insert(
            $this->_table(),
            array(
                'conference_id' => $conference_id,
                'category_title' => $category_name,
                'category_type' => $category_type
            )
        );
        return $this->db->insert_id();
    }

    /**
     * 找到某个会议的第一个category栏目。
     * @param int $conference_id
     * @param bool $display_order
     * @return int 0表示未找到。
     */
    public function get_first_category_id(int $conference_id, bool $display_order = FALSE): int
    {
        if ($display_order == FALSE) {
            $query = $this->db->query('
				SELECT MIN(category_id) 
				FROM ' . $this->_table() . ' WHERE `conference_id` = ' . strval($conference_id)
            );
            $qr = $query->result_array();
            $category_id = empty($qr) ? 0 : $qr[0]['MIN(category_id)'];
        } else {
            $query = $this->db->query('
				SELECT category_id 
				FROM ' . $this->_table() . ' 
				WHERE `conference_id` = ' . strval($conference_id) . ' 
				ORDER BY category_display_order, category_id ASC 
				LIMIT 1'
            );
            $qr = $query->result_array();
            $category_id = empty($qr) ? 0 : $qr[0]['category_id'];
        }
        return $category_id;
    }

    /**
     * 得到某个会议的按照display order排序的categories集合。
     * @see \myConf\Models\Category::get()
     * @param int $conference_id
     * @param bool $display_order
     * @param bool $from_db
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function get_categories_from_conference(int $conference_id, bool $display_order = TRUE, bool $from_db = false): array
    {
        $result = array();
        //先从缓存中取，失败再从数据库中读取。如果参数$from_db设置为true，无论如何从数据库读取，并刷新缓存
        if ($from_db === false) {
            try {
                //注意缓存键的设置，同时包含两个参数
                $result = $this->cache()->get('cat_list\\' . strval($conference_id) . strval($display_order));
            } catch (\myConf\Exceptions\CacheMissException $e) {
                $from_db = true;
            }
        }
        if ($from_db === true) {
            $query = $this->db->query('
			SELECT category_id, category_title, category_type, category_display_order 
			FROM ' . $this->_table() . ' WHERE conference_id = ' . strval($conference_id) .
                ($display_order ? ' ORDER BY category_display_order' : '')
            );
            $result = $query->result_array();
            $result = empty($result) ? array() : $result;
            $this->cache()->set('cat_list\\' . strval($conference_id) . strval($display_order), $result, 3600);
        }
        return $result;
    }

    /**
     * @param int $conference_id
     */
    public function cache_categories_delete(int $conference_id): void
    {
        $this->cache()->delete('cat_list\\' . strval($conference_id));
    }

    /**
     * @param int $category_id
     */
    public function delete_category(int $category_id): void
    {
        $this->db->where('category_id', $category_id);
        $this->db->delete($this->_table());
    }

    /**
     * 修改某个cateogry的名字。
     * @deprecated
     * @see \myConf\Models\Category::set()
     * @param int $category_id
     * @param string $new_category_name
     */
    public function rename_category(int $category_id, string $new_category_name): void
    {
        $this->set($category_id, array('category_title' => $new_category_name));
    }

    /**
     * 修改某个category的display_order。
     * @deprecated
     * @see \myConf\Models\Category::set()
     * @param $category_id
     * @param $display_order
     */
    public function set_category_display_order(int $category_id, int $display_order): void
    {
        $this->set($category_id, array('category_display_order' => $display_order));
    }
}