<?php

/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/13
 * Time: 22:50
 */
class sAttachment extends CF_Service
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $file_field
     * @param int $document_id
     * @return \sAttachment\sAttachmentUeditorUploadRet
     * @throws \sAttachment\UEditorUploadException
     */
    public function ueditor_upload(string $file_field, int $document_id): \sAttachment\sAttachmentUeditorUploadRet
    {
        try {
            $attach_info = $this->attach->parse_attach($file_field);
            $attachment_id = $this->mAttachment->add_attachment_from_document(
                $attach_info['full_name'],
                $attach_info['size'],
                $attach_info['original_name'],
                $document_id,
                $attach_info['is_image'],
                0,
                1
            );
            return new \sAttachment\sAttachmentUeditorUploadRet($attachment_id, $attach_info['original_name'], $attach_info['size'], $attach_info['extension'], $attach_info['full_name']);
        } catch (\lAttach\AttachParseException $e) {
            throw new \sAttachment\UEditorUploadException($e->getJsonStatus(), $e);
        }
    }

    /**
     * @param int $attachment_id
     * @param string $type
     * @throws \lAttach\AttachReadException
     * @throws \sAttachment\AttachmentIdNotFoundException
     */
    public function download_attachment(int $attachment_id, string $type = 'file'): void
    {
        try {
            $attach_info = $this->mAttachment->get_attachment($attachment_id);
            if ($type === 'file') {
                $this->mAttachment->increase_download_times($attachment_id);
            }
            $this->attach->download_attach($attach_info['attachment_file_name'], $attach_info['attachment_original_name'], $attach_info['attachment_file_size']);
        } catch (\DbNotFoundException $e) {
            throw new \sAttachment\AttachmentIdNotFoundException('ATTACHMENT_ID_NOT_FOUND', $e, 'AttachmentId ' . strval($attachment_id) . ' Not found.\n' . $e->getMessage());
        } catch (\lAttach\AttachReadException $e) {
            throw $e;
        }
    }

    /**
     * @param int $limit
     * @param int $start
     * @param bool $image_only
     * @return \sAttachment\sAttachmentUeditorGetListRet
     */
    public function ueditor_get_list(int $limit, int $start, bool $image_only = false): \sAttachment\sAttachmentUeditorGetListRet
    {
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