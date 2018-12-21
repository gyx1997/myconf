<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 23:40
 */

namespace myConf\Models;


class Document extends \myConf\BaseModel
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'documents';
        $this->_pk = 'document_id';
    }

    /**
     * @param int $category_id
     * @return array
     */
    public function get_documents_from_category(int $category_id): array
    {
        $this->db->where('document_category_id', $category_id);
        $this->db->select('*');
        $query = $this->db->get($this->_table());
        if (empty($query->result_array())) {
            return array();
        }
        return $query->result_array();
    }

    /**
     * @param int $category_id
     * @return array
     */
    public function get_first_document_from_category(int $category_id): array
    {
        return $this->_fetch_first(array('document_category_id' => $category_id));
    }

    /**
     * 根据指定的document_id获得document信息
     * @deprecated
     * @see \myConf\Models\Document::get()
     * @param int $document_id
     * @return array
     */
    public function get_document(int $document_id): array
    {
        return $this->get($document_id);
    }

    /**
     * 更新document
     * @deprecated
     * @see \myConf\Models\Document::set()
     * @param int $document_id
     * @param string $document_title
     * @param string $document_html
     */
    public function modify_document(int $document_id, string $document_title, string $document_html): void
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

    /**
     * 添加一个document
     * @param int $category_id
     * @param string $document_title
     * @param string $document_html
     * @return int
     */
    public function add_document(int $category_id, string $document_title, string $document_html): int
    {
        $this->db->insert(
            $this->_table(),
            array(
                'document_category_id' => $category_id,
                'document_title' => $document_title,
                'document_html' => $document_html
            )
        );
        return $this->db->insert_id();
    }
}