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
        public function new_draft(int $user_id, int $conference_id, string $title, string $abstract, array $authors, int $paper_aid, int $copyright_aid, string $type, string $suggested_session, string $custom_suggested_session = '') : int {
            return $this->Models->Paper->add($user_id, $conference_id, $authors, $paper_aid, $copyright_aid, $type, $suggested_session, $title, $abstract, $this->Models->Paper::paper_status_saved, $custom_suggested_session);
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
        public function save_draft(int $paper_id, int $paper_version, int $paper_aid, int $copyright_aid, array $authors, string $type, string $title, string $abstract, string $suggested_session, string $suggested_session_custom = '') : void {
            $this->Models->Paper->update_paper($paper_id, $paper_version, \myConf\Models\Paper::paper_status_saved , $authors, $paper_aid, $copyright_aid, $type, $suggested_session, $title, $abstract, $suggested_session_custom);
        }

        public function new_submit_version(
            int $old_paper_id, int $old_paper_version, int $user_id, int
        $conference_id,
            string $title, string $abstract,
            array $authors, int $paper_aid,
            int $copyright_aid, string $type,
            string $suggested_session, string
            $custom_suggested_session = '') : int {
            //增加一个版本号
            $this->Models->Paper->add($user_id, $conference_id, $authors, $paper_aid, $copyright_aid, $type, $suggested_session, $title, $abstract, \myConf\Models\Paper::paper_status_submitted, $custom_suggested_session, $old_paper_id, $old_paper_version + 1);
        }

        public function new_draft_version(int $old_paper_id, int $old_paper_version, int $user_id, int $conference_id, string $title, string $abstract, array $authors, int $paper_aid, int $copyright_aid, string $type, string $suggested_session, string $custom_suggested_session = '') : int {
            return $this->Models->Paper->add($user_id, $conference_id, $authors, $paper_aid, $copyright_aid, $type, $suggested_session, $title, $abstract, \myConf\Models\Paper::paper_status_saved, $custom_suggested_session, $old_paper_id, $old_paper_version + 1);
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
         * @param int $paper_id
         * @param int $paper_version
         * @param int $paper_aid
         * @param int $copyright_aid
         * @param array $authors
         * @param string $type
         * @param string $title
         * @param string $abstract
         * @param string $suggested_session
         * @param string $suggested_session_custom
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public function submit_paper(int $paper_id, int $paper_version, int $paper_aid, int $copyright_aid, array $authors, string $type, string $title, string $abstract, string $suggested_session, string $suggested_session_custom = '') : void {
            $this->Models->Paper->update_paper($paper_id, $paper_version, \myConf\Models\Paper::paper_status_submitted, $authors, $paper_aid, $copyright_aid, $type, $suggested_session, $title, $abstract, $suggested_session_custom);
            return;
        }

        /**
         * @param int $user_id
         * @param int $conference_id
         * @param string $title
         * @param string $abstract
         * @param array $authors
         * @param int $paper_aid
         * @param int $copyright_aid
         * @param string $type
         * @param string $suggested_session
         * @param string $custom_suggested_session
         * @return int
         */
        public function new_submit(int $user_id, int $conference_id, string $title, string $abstract, array $authors, int $paper_aid, int $copyright_aid, string $type, string $suggested_session, string $custom_suggested_session = '') : int {
            return $this->Models->Paper->add($user_id, $conference_id, $authors, $paper_aid, $copyright_aid, $type, $suggested_session, $title, $abstract, $this->Models->Paper::paper_status_submitted, $custom_suggested_session);
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


        /**
         * @param int $conference_id
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function get_conference_papers(int $conference_id) : array {
            $papers = $this->Models->Paper->get_conference_papers($conference_id);
            foreach($papers as &$paper) {
                $user_info = $this->Models->User->get_by_id($paper['user_id']);
                $scholar_info = $this->Models->Scholar->get_by_email($user_info['user_email']);
                $paper_session_info = $this->Models->PaperSession->get_session(intval($paper['paper_suggested_session']));
                $paper['review_status'] = $this->Models->PaperReview->get_paper_review_status($paper['paper_logic_id'], $paper['paper_version']);
                $paper['paper_suggested_session'] = $paper_session_info['session_text'];
                $paper['user_email'] = $user_info['user_email'];
                $paper['user_name'] = $scholar_info['scholar_first_name'] . ', ' . $scholar_info['scholar_last_name'];
            }
            return $papers;
        }

        /**
         * @param int $paper_id
         * @param int $paper_ver
         * @param string $reviewer_email
         * @return bool
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         * @throws \myConf\Exceptions\ReviewerAlreadyExistsException
         */
        public function add_reviewer(int $paper_id, int $paper_ver, string $reviewer_email) : bool {
            if ($this->Models->PaperReview->reviewer_exists($paper_id, $paper_ver, $reviewer_email)) {
                throw new \myConf\Exceptions\ReviewerAlreadyExistsException('REVIEWER_ALREADY_EXISTS', "The reviewer $reviewer_email has already added into the review list of paper {$paper_id}-{$paper_ver}.");
            }
            $this->Models->PaperReview->add_reviewer_to_paper($paper_id, $paper_ver, $reviewer_email);
            if ($this->Models->User->exist_by_email($reviewer_email) === FALSE) {
                $content = '
                        <h1>文章评审邀请函</h1>
                        <p>
                            会议CSQRWC的主办方邀请您参与会议的论文评审。请按照如下步骤进行操作。
                            如果您不知道这个会议，请忽略这封邮件。
                        </p>
                    ';
                \myConf\Libraries\Email::send_mail('Account@mail.myconf.cn', 'Account of myconf.cn', $reviewer_email, 'Invitation for paper review', $content);
                $paper = $this->Models->Paper->get_paper($paper_id, $paper_ver);
                /*
                $this->Callbacks->add('after_register', 'MarkUserAsReviewer', [
                    'conf_id' => $paper['conference_id'],
                    'user_email' => $reviewer_email,
                ]);
                */
                return FALSE;
            }
            return TRUE;
        }

        public function get_paper_review_details(int $paper_id, int $paper_version) : array {
            return $this->Models->PaperReview->get_paper_review_status($paper_id, $paper_version);
        }

        public function get_user_review_tasks(string $user_email, int $conference_id) : array {
            $tasks = $this->Models->PaperReview->get_reviewer_conference_tasks($user_email, $conference_id);
            foreach($tasks as &$paper) {
                $user_info = $this->Models->User->get_by_id($paper['user_id']);
                $scholar_info = $this->Models->Scholar->get_by_email($user_info['user_email']);
                $paper_session_info = $this->Models->PaperSession->get_session(intval($paper['paper_suggested_session']));
                $paper['paper_suggested_session'] = $paper_session_info['session_text'];
                $paper['user_email'] = $user_info['user_email'];
                $paper['user_name'] = $scholar_info['scholar_first_name'] . ', ' . $scholar_info['scholar_last_name'];
            }
            return $tasks;
        }

    }