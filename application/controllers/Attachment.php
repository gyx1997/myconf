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

    public function put()
    {
        $attach_info = $this->attach->parse_attach('upfile');
        $attachment_id = $this->mAttachment->add_attachment_from_document(
            $attach_info['full_name'],
            $attach_info['size'],
            $attach_info['original_name'],
            intval($this->input->post('document_id')),
            $attach_info['is_image'],
            0,
            1
        );
        $this->result = array(
            'state' => $attach_info['status'] === 'SUCCESS' ? 'SUCCESS' : $attach_info['error'],
            'url' => '/attachment/get/' . $this->_action . '/?aid=' . $attachment_id,
            'title' => $attach_info['short_name'],
            'original' => $attach_info['original_name'],
            'type' => $attach_info['extension'],
            'size' => $attach_info['size']
        );
        $this->return_json();
        return;
    }

    private function return_json()
    {
        header("Content-Type: text/html; charset=utf-8");
        $callback = $this->input->get('callback');
        echo isset($callback) ?
            htmlspecialchars($callback) . '(' . json_encode($this->result) . ')' :
            json_encode($this->result);
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

    public function get()
    {
        //从数据库中查找附件信息
        $attachment_id = intval($this->input->get('aid'));
        $attach_info = $this->mAttachment->get_attachment($attachment_id);
        if (empty($attach_info)) {
            show_404();
        }
        switch ($this->_action) {
            case 'file':
                {
                    $this->mAttachment->increase_download_times($attachment_id);
                }
            case 'image':
                {
                    $this->attach->download_attach(
                        $attach_info['attachment_file_name'],
                        $attach_info['attachment_original_name'],
                        $attach_info['attachment_file_size'],
                        $this->_action === 'image'
                    );
                    break;
                }
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
