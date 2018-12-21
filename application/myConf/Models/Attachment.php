<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/16
 * Time: 16:30
 */

namespace myConf\Models;


class Attachment extends \myConf\BaseModel
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

    /**
     * @param string $file_name
     * @param int $file_size
     * @param string $original_name
     * @param int $document_id
     * @param bool $is_image
     * @param int $image_width
     * @param int $image_height
     * @return int
     */
    public function add_attachment_from_document(string $file_name, int $file_size, string $original_name,
                                                 int $document_id = 0, bool $is_image = FALSE, int $image_width = 0, int $image_height = 0): int
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
     * 对于会议本身的附件添加（设置使用标记直接为1）
     * @param string $file_name
     * @param int $file_size
     * @param string $original_name
     * @param int $document_id
     * @param bool $is_image
     * @param int $image_width
     * @param int $image_height
     * @return int
     */
    public function add_attachment_from_conference(string $file_name, int $file_size, string $original_name,
                                                   int $conference_id = 0, bool $is_image = FALSE, int $image_width = 0, int $image_height = 0)
    {
        return $this->_add_attachment(array(
                'attachment_file_name' => $file_name,
                'attachment_is_image' => $is_image ? 1 : 0,
                'attachment_file_size' => $file_size,
                'attachment_original_name' => $original_name,
                'attachment_image_height' => empty($image_height) ? 0 : $image_height,
                'attachment_image_width' => empty($image_width) ? 0 : $image_width,
                'attachment_tag_id' => $conference_id,
                'attachment_tag_type' => 'conf',
                'attachment_used' => 1
            )
        );
    }

    /**
     * @param array $data
     * @return int
     */
    private function _add_attachment(array $data = array()): int
    {
        $this->db->insert($this->_table(), $data);
        return $this->db->insert_id();
    }

    /**
     * @param int $attachment_id
     * @return array
     */
    public function get_attachment(int $attachment_id): array
    {
        return $this->_fetch_first(array('attachment_id' => $attachment_id));
    }

    /**
     * @param int $attachment_id
     */
    public function increase_download_times(int $attachment_id): void
    {
        $attachment_id = intval($attachment_id);
        $this->db->query('UPDATE ' . $this->_table() . ' SET attachment_download_times=attachment_download_times+1 WHERE attachment_id=' . strval($attachment_id));
    }

    /**
     * @param string $attachment_tag_type
     * @param int $attachment_tag_id
     * @return array
     */
    public function get_used_attachments(string $attachment_tag_type, int $attachment_tag_id): array
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
     * 给指定序号的ID设置used状态
     * @param int $attachment_id
     * @param bool $used_status
     * @return bool
     */
    public function set_attachment_used(int $attachment_id, $used_status = TRUE): void
    {
        $attachment_id = intval($attachment_id);
        $this->db->query('UPDATE ' . $this->_table() . ' SET attachment_used=' . ($used_status ? '1' : '0') . ' WHERE attachment_id=' . strval($attachment_id));
    }

    /**
     * 得到文件列表
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