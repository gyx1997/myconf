<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/24
     * Time: 18:36
     */

    namespace myConf\Services;

    /**
     * Class Paper
     * @package myConf\Services
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     */
    class Paper extends \myConf\BaseService {

        /**
         * Paper constructor.
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * @param int $user_id
         * @param int $conference_id
         * @param string $title
         * @param string $abstract
         * @param array $authors
         * @param string $pdf_field
         * @param string $copyright_field
         * @param string $type
         * @param string $suggested_session
         * @param string $custom_suggested_session
         * @return int
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public function new_draft(int $user_id, int $conference_id, string $title, string $abstract, array $authors, string $pdf_field, string $copyright_field, string $type, string $suggested_session, string $custom_suggested_session = '') : int {
            //没有上传文件的话，就设置为空数组
            try {
                $paper_pdf = \myConf\Libraries\Attach::parse_attach($pdf_field);
            } catch (\myConf\Exceptions\FileUploadException $e) {
                $paper_pdf = [];
            }
            //没有上传Copyright文件的话，就设置为空数组
            try {
                $copyright_pdf = \myConf\Libraries\Attach::parse_attach($copyright_field);
            } catch (\myConf\Exceptions\FileUploadException $e) {
                $copyright_pdf = [];
            }
            return $this->Models->Paper->add($user_id, $conference_id, $authors, $paper_pdf, $copyright_pdf, $type, $suggested_session, $title, $abstract, $this->Models->Paper::paper_status_saved, $custom_suggested_session);
        }

        /**
         * 保存草稿
         * @param int $paper_id
         * @param int $paper_version
         * @param string $paper_field
         * @param string $copyright_field
         * @param array $authors
         * @param string $type
         * @param string $title
         * @param string $abstract
         * @param string $suggested_session
         * @param string $suggested_session_custom
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         * @throws \myConf\Exceptions\DbTransactionException
         * @throws \myConf\Exceptions\FileUploadException
         */
        public function save_draft(int $paper_id, int $paper_version, string $paper_field, string $copyright_field, array $authors, string $type, string $title, string $abstract, string $suggested_session, string $suggested_session_custom = '') : void {
            //没有更新的文件的话，就不更新
            try {
                $paper_pdf = \myConf\Libraries\Attach::parse_attach($paper_field);
            } catch (\myConf\Exceptions\FileUploadException $e) {
                if ($e->getShortMessage() === 'NO_SUCH_FILE') {
                    $paper_pdf = null;
                } else {
                    throw $e;
                }
            }
            try {
                $copyright_pdf = \myConf\Libraries\Attach::parse_attach($copyright_field);
            } catch (\myConf\Exceptions\FileUploadException $e) {
                if ($e->getShortMessage() === 'NO_SUCH_FILE') {
                    $copyright_pdf = null;
                } else {
                    throw $e;
                }
            }
            $this->Models->Paper->update_paper($paper_id, $paper_version, \myConf\Models\Paper::paper_status_saved , $authors, $paper_pdf, $copyright_pdf, $type, $suggested_session, $title, $abstract, $suggested_session_custom);
        }

        public function submit_new_version() : int {

        }

        /**
         * @param int $user_id
         * @param int $conference_id
         * @return array
         */
        public function get_user_conference_papers(int $user_id, int $conference_id) : array {
            return $this->Models->Paper->get_by_conference_id_and_user_id($conference_id, $user_id);
        }

        /**
         * @param int $paper_id
         * @param int $paper_version
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function get(int $paper_id, int $paper_version) : array {
            return $this->Models->Paper->get_paper($paper_id, $paper_version);
        }

        /**
         * 更新文章
         * @param int $paper_id
         * @param string $paper_field
         * @param string $copyright_field
         * @param array $authors
         * @param string $type
         * @param string $title
         * @param string $abstract
         * @param string $suggested_session
         * @param string $suggested_session_custom
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbTransactionException
         * @throws \myConf\Exceptions\FileUploadException
         */
        public function submit_paper(int $paper_id, int $paper_version, string $paper_field, string $copyright_field, array $authors, string $type, string $title, string $abstract, string $suggested_session, string $suggested_session_custom = '') : void {
            //先获取旧的信息
            $old_paper = $this->Models->Paper->get_paper($paper_id, $paper_version);
            //没有更新的文件的话，需要判断是否已经有文件了
            try {
                $paper_pdf = \myConf\Libraries\Attach::parse_attach($paper_field);
            } catch (\myConf\Exceptions\FileUploadException $e) {
                if ($e->getShortMessage() === 'NO_SUCH_FILE' && intval($old_paper['pdf_attachment_id'] !== 0)) {
                    $paper_pdf = null;
                } else {
                    throw $e;
                }
            }
            try {
                $copyright_pdf = \myConf\Libraries\Attach::parse_attach($copyright_field);
            } catch (\myConf\Exceptions\FileUploadException $e) {
                if ($e->getShortMessage() === 'NO_SUCH_FILE' && intval($old_paper['copyright_attachment_id'] !== 0)) {
                    $copyright_pdf = null;
                } else {
                    throw $e;
                }
            }
            $this->Models->Paper->update_paper($paper_id, $paper_version, \myConf\Models\Paper::paper_status_submitted, $authors, $paper_pdf, $copyright_pdf, $type, $suggested_session, $title, $abstract, $suggested_session_custom);
            return;
        }

        public function new_submit(int $user_id, int $conference_id, string $title, string $abstract, array $authors, string $pdf_field, string $copyright_field, string $type, string $suggested_session, string $custom_suggested_session = '') : int {
            $paper_pdf = \myConf\Libraries\Attach::parse_attach($pdf_field);
            $copyright_pdf = \myConf\Libraries\Attach::parse_attach($copyright_field);
            return $this->Models->Paper->add($user_id, $conference_id, $authors, $paper_pdf, $copyright_pdf, $type, $suggested_session, $title, $abstract, $this->Models->Paper::paper_status_submitted, $custom_suggested_session);
        }

        /**
         * @param int $paper_logic_id
         * @param int $paper_version
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public function delete_paper(int $paper_logic_id, int $paper_version) : void {
            $paper = $this->Models->Paper->get_paper($paper_logic_id, $paper_version);
            //检查paper本身的附件是否存在，如果存在则删除之
            $attachment_pdf = $this->Models->Attachment->get($paper['pdf_attachment_id']);
            if(!empty($attachment_pdf)) {
                $pdf_file = ATTACHMENT_DIR . $attachment_pdf['attachment_file_name'];
                file_exists($pdf_file) && @unlink($pdf_file);
            }
            //检查paper的copyright是否存在，如果存在那么删除之
            $attachment_copyright = $this->Models->Attachment->get($paper['copyright_attachment_id']);
            if (!empty($attachment_copyright)) {
                $copyright_file = ATTACHMENT_DIR . $attachment_copyright['attachment_file_name'];
                file_exists($copyright_file) && @unlink($copyright_file);
            }
            //从数据库中删除记录
            $this->Models->Paper->delete_paper($paper_logic_id, $paper_version);
            return;
        }

    }