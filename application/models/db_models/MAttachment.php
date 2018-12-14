<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/23
 * Time: 16:33
 */

/**
 * Class mAttachment
 */
class mAttachment extends CF_Model
{
    public $tag_types = array('document' => 'document', 'paper' => 'paper', 'conf' => 'conf');
    public const tag_type_conference = 'conf';
    public const tag_type_document = 'document';
    public const tag_type_paper = 'paper';
    public const tag_type_non_restrict = '';

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'attachments';
    }

    public function add_attachment_from_document($file_name, $file_size, $original_name,
                                                 $document_id = 0, $is_image = FALSE, $image_width = 0, $image_height = 0)
    {
        return $this->_add_attachment(array(
                'attachment_file_name' => $file_name,
                'attachment_is_image' => $is_image ? 1 : 0,
                'attachment_file_size' => $file_size,
                'attachment_original_name' => $original_name,
                'attachment_image_height' => empty($image_height) ? 0 : $image_height,
                'attachment_image_width' => empty($image_width) ? 0 : $image_width,
                'attachment_tag_id' => $document_id,
                'attachment_tag_type' => 'document'
            )
        );
    }

    /**
     * @param $data
     * @return int
     */
    private function _add_attachment($data)
    {
        $this->db->insert($this->_table(), $data);
        return $this->db->insert_id();
    }

    /**
     * @param int $attachment_id
     * @return array
     * @throws DbNotFoundException
     */
    public function get_attachment(int $attachment_id)
    {
        return $this->_fetch_first(array('attachment_id' => $attachment_id));
    }

    /**
     * @param $attachment_id
     */
    public function increase_download_times($attachment_id)
    {
        $attachment_id = intval($attachment_id);
        $this->db->query('UPDATE ' . $this->_table() . ' SET attachment_download_times=attachment_download_times+1 WHERE attachment_id=' . strval($attachment_id));
    }

    /**
     * @param $attachment_tag_type
     * @param $attachment_tag_id
     * @return array
     */
    public function get_used_attachments($attachment_tag_type, $attachment_tag_id)
    {
        return $this->_fetch_all(
            array(
                'attachment_tag_type' => $attachment_tag_type,
                'attachment_tag_id' => $attachment_tag_id,
                'attachment_used' => 1
            )
        );
    }

    /**
     * @param $attachment_id
     * @param bool $used_status
     */
    public function set_attachment_used($attachment_id, $used_status = TRUE)
    {
        $attachment_id = intval($attachment_id);
        $this->db->query('UPDATE ' . $this->_table() . ' SET attachment_used=' . ($used_status ? '1' : '0') . ' WHERE attachment_id=' . strval($attachment_id));
    }

    /**
     * @param string $tag_type
     * @param int $tag_id
     * @param bool $image_only
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function get_file_list(string $tag_type = '', int $tag_id = 0, bool $image_only = FALSE, int $start = 0, int $limit = 10): array
    {
        $this->db->select('*');
        if ($tag_type !== '' && isset($this->tag_types[$tag_type])) {
            $this->db->where('attachment_tag_type', $this->tag_types[$tag_type]);
            if ($tag_id !== 0) {
                $this->db->where('attachment_tag_id', $tag_id);
            }
        }
        $image_only === TRUE && $this->db->where('attachment_is_image', 1);
        $this->db->limit($limit, strval($start));
        $this->db->order_by('attachment_id', 'DESC');
        $query_result = $this->db->get($this->_table());
        return $query_result->result_array();
    }
}
