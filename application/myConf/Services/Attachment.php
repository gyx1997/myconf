<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/23
     * Time: 22:49
     */

    namespace myConf\Services;

    class Attachment extends \myConf\BaseService {

        public function __construct() {
            parent::__construct();
        }

        /**
         * @param string $file_field
         * @param int $document_id
         * @return array
         * @throws \myConf\Exceptions\FileUploadException
         */
        public function ueditor_upload(string $file_field, int $document_id) : array {
            $attach_info = \myConf\Libraries\Attach::parse_attach($file_field);
            $attachment_id = $this->Models->Attachment->add_as_document_attached($document_id, $attach_info['full_name'], $attach_info['original_name'], $attach_info['size'], $attach_info['is_image']);
            return [
                'attachment_id' => $attachment_id,
                'original_name' => $attach_info['original_name'],
                'file_size' => $attach_info['size'],
                'extension' => $attach_info['extension'],
                'file_name' => RELATIVE_ATTACHMENT_DIR . $attach_info['full_name'],
            ];
        }

        /**
         * @param int $attachment_id
         * @param string $type
         * @throws \myConf\Exceptions\AttachFileCorruptedException
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\SendExitInstructionException
         */
        public function download_attachment(int $attachment_id, string $type = 'file') : void {
            $attach_info = $this->Models->Attachment->get($attachment_id);
            if ($type === 'file') {
                $this->Models->Attachment->increase_download_times($attachment_id);
            }
            \myConf\Libraries\Attach::download_attach($attach_info['attachment_file_name'], $attach_info['attachment_original_name'], $attach_info['attachment_file_size'], \myConf\Libraries\Attach::get_mode_download);
            throw new \myConf\Exceptions\SendExitInstructionException('DOWNLOAD_OK', 'Download success.');
        }

        /**
         * @param int $attachment_id
         * @throws \myConf\Exceptions\AttachFileCorruptedException
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\SendExitInstructionException
         */
        public function download_pdf_as_preview(int $attachment_id) : void {
            $attach_info = $this->Models->Attachment->get($attachment_id);
            \myConf\Libraries\Attach::preview_attach($attach_info['attachment_file_name'], $attach_info['attachment_original_name'], $attach_info['attachment_file_size'], \myConf\Libraries\Attach::file_type_pdf);
            throw new \myConf\Exceptions\SendExitInstructionException('DOWNLOAD_OK', 'Download success.');
        }

        /**
         * @param int $limit
         * @param int $start
         * @param bool $image_only
         * @return \sAttachment\sAttachmentUeditorGetListRet
         */
        public function ueditor_get_list(int $limit, int $start, bool $image_only = false) : \sAttachment\sAttachmentUeditorGetListRet {
            $files = $this->mAttachment->get_file_list($this->mAttachment::tag_type_document, 0, $image_only, $start, $limit);
            if (count($files) === 0) {
                return new \sAttachment\sAttachmentUeditorGetListRet('no match file', 0, array());
            }
            $file_list = array();
            foreach ($files as $file) {
                $file_list [] = array(
                    'url' => $file['attachment_file_name'],
                    'mtime' => $file['attachment_upload_time'],
                    'original' => $file['attachment_original_name'],
                );
            }
            return new \sAttachment\sAttachmentUeditorGetListRet('SUCCESS', count($files), $file_list);
        }
    }