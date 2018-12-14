<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/23
 * Time: 16:31
 */

class attachment extends CF_Controller
{
    private $result = array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 上传文件
     */
    public function put()
    {
        try {
            $attach_data = $this->sAttachment->ueditor_upload('upfile', $this->input->post('document_id'));
            $this->_exit_with_json(
                array(
                    'state' => 'SUCCESS',
                    //对于图片直接进行下载，不需要中转进行文件重命名。
                    //也可以充分利用后期的CDN
                    'url' => $this->_action === 'file' ? '/attachment/get/' . $this->_action . '/?aid=' . $attach_data->attachment_id : $attach_data->stored_full_name,
                    'title' => $attach_data->original_file_name,
                    'original' => $attach_data->original_file_name,
                    'type' => $attach_data->file_extension,
                    'size' => $attach_data->file_size,
                )
            );
        } catch (\sAttachment\UEditorUploadException $e) {
            $this->_exit_with_json(array('state' => $e->getJsonStatus()));
        }
        return;
    }

    /**
     * 返回UE带有callback的Json
     * @param array $data
     */
    protected function _exit_with_json(array $data = array()): void
    {
        $callback = $this->input->get('callback');
        if (isset($callback)) {
            header("Content-Type: text/html; charset=utf-8");
            exit(htmlspecialchars($callback) . '(' . json_encode($data) . ')');
        } else {
            parent::_exit_with_json($data);
        }
    }

    /**
     * 获取文件列表
     */
    public function get_list()
    {
        $limit = intval($this->input->get('size'));
        $start = intval($this->input->get('start'));
        $ret = $this->sAttachment->ueditor_get_list($limit, $start, $this->_action === 'image');
        $this->_exit_with_json(array('state' => $ret->status, 'list' => $ret->file_list, 'start' => $start, 'total' => $ret->total));
    }

    /**
     * 下载文件
     */
    public function get()
    {
        try {
            $attachment_id = intval($this->input->get('aid'));
            $type = ($this->_action === 'image' ? 'image' : 'file');
            $this->sAttachment->download_attachment($attachment_id, $type);
        } catch (\sAttachment\AttachmentIdNotFoundException $e) {
            show_error('404 Not Found. The attachment you requested was not found, or it has been deleted.', 404);
        } catch (\lAttach\AttachReadException $e) {
            show_error('404 Not Found. The attachment you requested was not found, or it has been deleted.', 404);
        }
    }
}
