<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/23
     * Time: 23:26
     */

    namespace myConf\Controllers;

    class Attachment extends \myConf\BaseController {

        /**
         * Attachment constructor.
         * @throws \Exception
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * @throws \myConf\Exceptions\SendExitInstructionException
         */
        public function put() {
            try {
                $attach_data = $this->Services->Attachment->ueditor_upload('upfile', $this->input->post('document_id'));
                $this->exit_promptly([
                    'state' => 'SUCCESS',
                    //对于图片直接进行下载，不需要中转进行文件重命名。
                    //也可以充分利用后期的CDN
                    'url' => $this->_action === 'file' ? '/attachment/get/' . $this->_action . '/?aid=' . $attach_data['attachment_id'] : $attach_data['file_name'],
                    'title' => $attach_data['original_name'],
                    'original' => $attach_data['original_name'],
                    'type' => $attach_data['extension'],
                    'size' => $attach_data['file_size'],
                    'aid' => $attach_data['attachment_id'],
                ]);
            } catch (\myConf\Exceptions\FileUploadException $e) {
                $this->exit_promptly();
            }
            return;
        }

        /**
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\HttpStatusException
         * @throws \myConf\Exceptions\SendExitInstructionException
         */
        public function get() {
            try {
                $attachment_id = intval($this->input->get('aid'));
                $type = ($this->_action === 'image' ? 'image' : 'file');
                $this->Services->Attachment->download_attachment($attachment_id, $type);
            } catch (\myConf\Exceptions\AttachFileCorruptedException $e) {
                throw new \myConf\Exceptions\HttpStatusException(404, 'ATTACH_NOT_FOUND', 'The requested attachment does not exist, it may have been deleted or moved before.');
            }
        }

        /**
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\HttpStatusException
         * @throws \myConf\Exceptions\SendExitInstructionException
         */
        public function preview() {
            try {
                $attachment_id = intval($this->input->get('aid'));
                if ($this->_action === 'pdf') {
                    $this->Services->Attachment->download_pdf_as_preview($attachment_id);
                } else {
                    throw new \myConf\Exceptions\HttpStatusException(404, 'FILE_NOT_FOUND', 'The file you have requested for preview does not exists, it may have been deleted or moved before.');
                }
            } catch (\myConf\Exceptions\AttachFileCorruptedException $e) {
                throw new \myConf\Exceptions\HttpStatusException(404, 'ATTACH_NOT_FOUND', 'The file you have requested for preview does not exists, it may have been deleted or moved before.');
            }
        }
    }