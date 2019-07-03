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
    public const tag_type_none = 'none';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $document_id
     * @param string $full_name
     * @param string $original_name
     * @param int $file_size
     * @param bool $is_image
     * @param bool $used
     * @return int
     */
    public function add_as_document_attached(int $document_id, string $full_name, string $original_name, int $file_size, bool $is_image = false) : int
    {
        return $this->add($full_name, $file_size, $original_name, 'document', $document_id, $is_image, 0, 0, 0);
    }

    /**
     * 添加一个未知分类的附件
     * @param string $full_name
     * @param string $original_name
     * @param int $file_size
     * @param bool $is_image
     * @return int
     */
    public function add_as_unknown_attached(string $full_name, string $original_name, int $file_size, bool $is_image = false) : int {
        return $this->add($full_name, $file_size, $original_name, self::tag_type_none, 0, $is_image, 0, 0, 0);
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
     * @param string $file_name
     * @param int $file_size
     * @param string $original_name
     * @param string $tag_type
     * @param int $tag_id
     * @param bool $is_image
     * @param bool $used
     * @param int $image_width
     * @param int $image_height
     * @return int
     */
    public function add(string $file_name, int $file_size, string $original_name, string $tag_type, int $tag_id = 0, bool $is_image = false, bool $used = false, int $image_width = 0, int $image_height = 0) {
        return $this->Tables->Attachments->insert(array(
            'attachment_file_name' => $file_name,
            'attachment_is_image' => $is_image ? 1 : 0,
            'attachment_file_size' => $file_size,
            'attachment_original_name' => $original_name,
            'attachment_image_height' => empty($image_height) ? 0 : $image_height,
            'attachment_image_width' => empty($image_width) ? 0 : $image_width,
            'attachment_tag_id' => $tag_id,
            'attachment_tag_type' => $tag_type,
            'attachment_used' => $used ? 1 : 0,
            'attachment_filename_hash' => crc32($file_name),
        ));
    }

    /**
     * @param string $filename
     * @return int
     */
    public function get_id_from_filename(string $filename) : int {
        $img = $this->Tables->Attachments->fetch_first([
            'attachment_filename_hash' => crc32($filename),
            'attachment_file_name' => $filename,
        ]);
        return empty($img) ? 0 : $img['attachment_id'];
    }

    /**
     * @param int $attachment_id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function get(int $attachment_id) : array
    {
        return $this->Tables->Attachments->get(strval($attachment_id));
    }

    /**
     * @param int $attachment_id
     * @throws \myConf\Exceptions\CacheDriverException
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
     * 获取某一类的未使用的附件
     * @param string $tag_type
     * @param int $tag_id
     * @return array
     */
    public function get_unused(string $tag_type, int $tag_id) : array {
        return $this->Tables->Attachments->fetch_all([
            'attachment_tag_type' => $tag_type,
            'attachment_tag_id' => $tag_id,
            'attachment_used' => 0,
        ]);
    }

    /**
     * @param int $attachment_id
     * @param bool $used_status
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function set_used_status(int $attachment_id, bool $used_status = true) : void
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
        $where = ['attachment_tag_type' => $tag_type, 'attachment_tag_id' => $tag_id];
        $image_only === true && $where['attachment_is_image'] = 1;
        return $this->Tables->Attachments->fetch_all($where, '', '', $start, $limit);
    }
}