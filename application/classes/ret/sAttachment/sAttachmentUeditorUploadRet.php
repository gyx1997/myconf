<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 18:59
 */

namespace sAttachment;

/**
 * Class sAttachmentUeditorUploadRet
 * @package sAttachment
 * @property int $attachment_id
 * @property int $file_size
 * @property string $original_file_name
 * @property string $file_extension
 * @property string $stored_full_name
 */
class sAttachmentUeditorUploadRet
{
    /**
     * sAttachmentUeditorUploadRet constructor.
     * @param int $attach_id
     * @param string $original_file_name
     * @param int $file_size
     * @param string $file_extension
     * @param string $stored_full_name
     */
    public function __construct(int $attach_id, string $original_file_name, int $file_size, string $file_extension, string $stored_full_name)
    {
        $this->attachment_id = $attach_id;
        $this->original_file_name = $original_file_name;
        $this->file_size = $file_size;
        $this->file_extension = $file_extension;
        $this->stored_full_name = $stored_full_name;
    }
}