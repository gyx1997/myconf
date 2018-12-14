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
            $attach_data = $this->sAttachment->ueditor_upload('file', $this->input->post('document_id'));
            $this->_exit_with_json(
                array(
                    'state' => 'SUCCESS',
                    'url' => '/attachment/get/' . $this->_action . '/?aid=' . $attach_data->attachment_id,
                    'title' => $attach_data->original_file_name,
                    'original' => $attach_data->original_file_name,
                    'type' => $attach_data->file_extension,
                    'size' => $attach_data->file_size,
                )
            );
        } catch (\sAttachment\UEditorUploadException $e) {
            $this->_exit_with_json(array('state' => $e->getJsonStatus()));
        } catch (\Exception $e) {
            //未捕获的系统异常
            show_error('Internal Server Error.');
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

    public function get_list()
    {
        $limit = $this->input->get('size');
        $start = $this->input->get('start');
        $files = $this->mAttachment->get_file_list(
            ($this->_action == 'image'),
            $start,
            $limit
        );
        if (empty($files)) {
            $this->result = array(
                'state' => 'no match file',
                'list' => array(),
                'start' => $start,
                'total' => 0
            );
        } else {
            $file_list = array();
            foreach ($files as $file) {
                $file_list [] = array(
                    'url' => $file['attachment_file_name'],
                    'mtime' => $file['attachment_upload_time'],
                    'original' => $file['attachment_original_name'],
                );
            }
            $this->result = array(
                'state' => 'SUCCESS',
                'list' => $file_list,
                'start' => $start,
                'total' => count($file_list)
            );
        }
        $this->return_json();
        return;
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
            show_404('The attachment you requested was not found.');
        }
    }

    private function get_file_short_name($file_full_name)
    {
        return substr(
            $file_full_name,
            strrpos($file_full_name, '/') + 1
        );
    }

}
