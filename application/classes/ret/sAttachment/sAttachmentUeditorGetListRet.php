<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 23:40
 */

namespace sAttachment;

/**
 * Class sAttachmentUeditorGetListRet
 * @package sAttachment
 * @property string $status
 * @property int $total
 * @property array $file_list
 */
class sAttachmentUeditorGetListRet
{
    public function __construct(string $json_status, int $total, array $file_list)
    {
        $this->status = $json_status;
        $this->total = $total;
        $this->file_list = $file_list;
    }
}