<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2019/4/23
     * Time: 0:49
     */

    namespace myConf\Services;

    class PaperReview extends \myConf\BaseService {

        /**
         * @param string $reviewer_email
         * @param int $paper_id
         * @param int $paper_version
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function reviewer_enter_review(string $reviewer_email, int $paper_id, int $paper_version) : void {
            $this->Models->PaperReview->enter_review($paper_id, $paper_version, $reviewer_email);
        }

        /**
         * @param string $reviewer_email
         * @param int $paper_id
         * @param int $paper_version
         * @return bool
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function reviewer_exists_in_paper(string $reviewer_email, int $paper_id, int $paper_version) : bool {
            return $this->Models->PaperReview->reviewer_exists($paper_id, $paper_version, $reviewer_email);
        }

        public function reviewer_save_review(string $reviewer_email, int
        $paper_id, int $paper_version, string $review_action, string
        $review_comment) : void {
            $this->Models->PaperReview->save_review($paper_id,
                $paper_version, $reviewer_email, $review_action,
                $review_comment);
        }

        public function reviewer_submit_review(string $reviewer_email, int
        $paper_id, int $paper_version, string $review_action, string
                                               $review_comment) : void {
            $this->Models->PaperReview->submit_review($paper_id,
                $paper_version, $reviewer_email, $review_action,
                $review_comment);
        }

        /**
         * 编辑结束审稿
         * @param int $paper_id
         * @param int $paper_ver
         * @param string $review_result
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function editor_finish_review(int $paper_id, int $paper_ver, string $review_result, string $comment = '') : void {
            $arr_result_mapping = [
                'reject' => \myConf\Models\Paper::paper_status_rejected,
                'accept' => \myConf\Models\Paper::paper_status_passed,
                'revision' => \myConf\Models\Paper::paper_status_revision,
            ];
            $this->Models->PaperReview->editor_finished_review($paper_id, $paper_ver, $arr_result_mapping[$review_result]);
            $paper = $this->Models->Paper->get_paper($paper_id, $paper_ver);
            $author_info = $this->Models->User->get_by_id($paper['user_id']);
            $conf_info = $this->Models->Conference->get_by_id($paper['conference_id']);
            if ($review_result == 'reject') {
                $content = 'Your paper is rejected.';
            } else if ($review_result == 'accept') {
                $content = 'Your paper is accepted.';
            } else {
                $content = 'Your paper is accepted with revision. You need to re-submit a new version.';
            }
            \myConf\Libraries\Email::send_mail('PaperReview@myconf.cn', 'PaperReview', $author_info['user_email'], 'Paper acceptance notice for conference ' . $conf_info['conference_name'], $content);
        }

        public function get_review_status(int $paper_id, int $paper_version,
                                          string $reviewer_email
    ) : array {
            return $this->Models->PaperReview->get_reviewer_review_status
            ($paper_id,
                $paper_version, $reviewer_email);
        }

    }