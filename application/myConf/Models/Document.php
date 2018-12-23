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
     * @see \myConf\Models\Documents::get()
     * @param int $document_id
     * @return array
     */
    public function get_document(int $document_id): array
    {
        return $this->get($document_id);
    }

    /**
     * 得到某个id的document
     * @param int $id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function get_by_id(int $id) : array {
        return $this->Tables->Documents->get(strval($id));
    }

    /**
     * 根据指定的id更新document的内容
     * @param int $id
     * @param string $content
     * @param string $title
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function update_content_by_id(int $id, string $content = '', string $title = '') : void {
        $this->Tables->Documents->set(strval($id), ['document_html' => $content, 'document_title' => $title]);
    }

    /**
     * 将document从显示列表上移一位
     * @param int $id
     */
    public function move_up(int $id) : void {
        //todo: add method body
    }

    /**
     * 将document从显示列表下移一位
     * @param int $id
     */
    public function move_down(int $id) : void {
        //todo: add method body.
    }

    /**
     * 更新document
     * @deprecated
     * @see \myConf\Models\Documents::set()
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