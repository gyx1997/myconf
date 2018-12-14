<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/28
 * Time: 19:43
 */

/**
 * Class mDocument
 */
class mDocument extends CF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'documents';
    }


    public function get_documents_from_category($category_id)
    {
        $this->db->where('document_category_id', $category_id);
        $this->db->select('*');
        $query = $this->db->get($this->_table());
        return $query->result_array();
    }

    public function get_first_document_from_category($category_id)
    {
        return $this->_fetch_first(array('document_category_id' => $category_id));
    }

    public function get_document($document_id)
    {
        $query = $this->db->query(
            'SELECT * FROM ' . $this->_table()
            . ' WHERE document_id = ' . intval($document_id)
            . ' LIMIT 1'
        );
        $r = $query->result_array();
        return $r[0];
    }

    public function modify_document($document_id, $document_title, $document_html)
    {
        $this->db->where('document_id', $document_id);
        $this->db->update(
            $this->_table(),
            array(
                'document_title' => $document_title,
                'document_html' => $document_html
            )
        );
    }

    public function add_document($category_id, $document_title, $document_html)
    {
        $this->db->insert(
            $this->_table(),
            array(
                'document_category_id' => $category_id,
                'document_title' => $document_title,
                'document_html' => $document_html
            )
        );
    }
}