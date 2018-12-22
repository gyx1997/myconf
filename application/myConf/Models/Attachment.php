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
    }

    /**
     * @param string $tag_type
     * @param int $tag_id
     * @param string $full_name
     * @param string $original_name
     * @param int $file_size
     * @param bool $is_image
     * @param bool $used
     * @return int
     */
    public function add_new(string $tag_type, int $tag_id, string $full_name, string $original_name, int $file_size, bool $is_image = false, bool $used = true) : int
    {
        return $this->Tables->Attachments->insert(array(
                'attachment_file_name' => $full_name,
                'attachment_is_image' => $is_image ? 1 : 0,
                'attachment_file_size' => $file_size,
                'attachment_original_name' => $original_name,
                'attachment_image_height' => 0, //unused
                'attachment_image_width' => 0,  //unused
                'attachment_tag_id' => $tag_id,
                'attachment_tag_type' => $tag_type,
                'attachment_used' => $used ? 1 : 0,
            )
        );
    }

    /**
     * 对于会议本身的附件添加（设置使用标记直接为1）
     * @param string $file_name
     * @param int $file_size
     * @param string $original_name
     * @param int $conference_id
     * @param bool $is_image
     * @param int $image_width
     * @param int $image_height
     * @return int
     */
    public function add_as_conference(string $file_name, int $file_size, string $original_name, int $conference_id = 0, bool $is_image = false, int $image_width = 0, int $image_height = 0)
    {
        return $this->Tables->Attachments->insert(array(
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
     * @param int $attachment_id
     * @return array
     */
    public function get(int $attachment_id) : array
    {
        return $this->Tables->Attachments->get(strval($attachment_id));
    }

    /**
     * @param int $attachment_id
     */
    public function increase_download_times(int $attachment_id): void
    {
        $this->Tables->Attachments->self_increase($attachment_id, 'attachment_download_times');
    }

    /**
     * @param string $attachment_tag_type
     * @param int $attachment_tag_id
     * @return array
     */
    public function get_used(string $attachment_tag_type, int $attachment_tag_id) : array
    {
        return $this->Tables->Attachments->fetch_all(
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
     */
    public function set_used(int $attachment_id, bool $used_status = true) : void
    {
        $this->Tables->Attachments->set(strval($attachment_id), array('attachment_used' => ($used_status ? '1' : '0')));
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
    public function get_list(string $tag_type = '', int $tag_id = 0, bool $image_only = false, int $start = 0, int $limit = 10) : array
    {
        return $this->Tables->Attachments->get_list($tag_type, $tag_id, $image_only, $start, $limit);
    }
}