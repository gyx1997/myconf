<?php

/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/20
 * Time: 21:39
 */
class mCategory extends CF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'categories';
    }

    public function has_category($category_id)
    {
        return $this->_exists(array('category_id' => $category_id));
    }

    public function add_category($conference_id, $category_name, $category_type)
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

    public function get_first_category_id($conference_id, $display_order = FALSE)
    {
        if ($display_order == FALSE) {
            $query = $this->db->query('
				SELECT MIN(category_id) 
				FROM ' . $this->_table() . ' WHERE `conference_id` = ' . intval($conference_id)
            );
            $qr = $query->result_array();
            $category_id = empty($qr) ? 0 : $qr[0]['MIN(category_id)'];
        } else {
            $query = $this->db->query('
				SELECT category_id 
				FROM ' . $this->_table() . ' 
				WHERE `conference_id` = ' . intval($conference_id) . ' 
				ORDER BY category_display_order, category_id ASC 
				LIMIT 1'
            );
            $qr = $query->result_array();
            $category_id = empty($qr) ? 0 : $qr[0]['category_id'];
        }
        return $category_id;
    }

    public function get_all_categories($conference_id, $display_order = TRUE)
    {
        $query = $this->db->query('
			SELECT category_id, category_title, category_type, category_display_order 
			FROM ' . $this->_table() . ' WHERE conference_id = ' . intval($conference_id) .
            ($display_order ? ' ORDER BY category_display_order' : '')
        );
        return $query->result_array();
    }

    public function delete_category($category_id)
    {
        $this->db->where('category_id', $category_id);
        $this->db->delete($this->_table());
    }

    public function rename_category($category_id, $new_category_name)
    {
        $new_data = array('category_title' => $new_category_name);
        $this->db->where('category_id', $category_id);
        $this->db->update($this->_table(), $new_data);
    }

    public function set_category_display_order($category_id, $display_order)
    {
        $this->db->where('category_id', $category_id);
        $this->db->update(
            $this->_table(),
            array(
                'category_display_order' => $display_order
            )
        );
    }
}
