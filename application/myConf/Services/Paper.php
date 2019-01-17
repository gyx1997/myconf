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
         * @throws \myConf\Exceptions\DbTransactionException
         * @throws \myConf\Exceptions\FileUploadException
         */
        public function submit_new(int $user_id, int $conference_id, string $title, string $abstract, array $authors, string $pdf_field, string $copyright_field, string $type, string $suggested_session, string $custom_suggested_session = '') : int {
            $pdf_result = \myConf\Libraries\Attach::parse_attach($pdf_field);
            $copyright_result = \myConf\Libraries\Attach::parse_attach($copyright_field);
            return $this->Models->Paper->add($user_id, $conference_id, $authors, $pdf_result, $copyright_result, $type, $suggested_session, $title, $abstract, $this->Models->Paper::paper_status_submitted, $custom_suggested_session);
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
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function get_paper(int $paper_id) : array {
            return $this->Models->Paper->get_paper_by_id($paper_id);
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
        public function update_paper(int $paper_id, string $paper_field, string $copyright_field, array $authors, string $type, string $title, string $abstract, string $suggested_session, string $suggested_session_custom = '') : void {
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
            $this->Models->Paper->update_paper_by_id($paper_id, $authors, $paper_pdf, $copyright_pdf, $type, $suggested_session, $title, $abstract, $suggested_session_custom);
            return;
        }

        /**
         * @param int $paper_id
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public function delete_paper(int $paper_id) : void {
            $paper = $this->Models->Paper->get_paper_by_id($paper_id);
            $attachment_pdf = $this->Models->Attachment->get($paper['pdf_attachment_id']);
            $attachment_copyright = $this->Models->Attachment->get($paper['copyright_attachment_id']);
            $pdf_file = ATTACHMENT_DIR . $attachment_pdf['attachment_file_name'];
            $copyright_file = ATTACHMENT_DIR . $attachment_copyright['attachment_file_name'];
            file_exists($pdf_file) && @unlink($pdf_file);
            file_exists($copyright_file) && @unlink($copyright_file);
            $this->Models->Paper->delete_paper($paper_id);
            return;
        }

    }