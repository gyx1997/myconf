<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2019/2/24
     * Time: 10:02
     */

    namespace myConf\Models;

    /**
     * Class PaperReview
     * @package myConf\Models
     * @author _g63<522975334@qq.com>
     * @version 2019.3
     */
    class PaperReview extends \myConf\BaseModel {

        public const review_status_before_review = 0;
        public const review_status_under_review = 1;
        public const review_status_finished_review = 2;

        public const review_result_unknown = 'UNKNOWN';
        public const review_result_passed = 'PASSED';
        public const review_result_revision = 'REVISION';
        public const review_result_reject = 'REJECTED';

        /**
         * PaperReview constructor.
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 获取某一文章所有的review记录
         * @param int $paper_id
         * @return array
         */
        public function get_paper_review_status(int $paper_id, int $paper_version) : array {
            return $this->Tables->PaperReview->fetch_all(['paper_id' => $paper_id, 'paper_version' => $paper_version]);
        }

        /**
         * @param int $paper_id
         * @param int $paper_ver
         * @param string $reviewer_email
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function get_reviewer_review_status(int $paper_id, int
        $paper_ver, string $reviewer_email) :
        array {
            return $this->Tables->PaperReview->get(['paper_id' => $paper_id, 'paper_version' => $paper_ver, 'reviewer_email' => $reviewer_email]);
        }

        /**
         * 编辑添加一个reviewer
         * @param int $paper_id
         * @param string $reviewer_email
         * @return int
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function add_reviewer_to_paper(int $paper_id, int $paper_version, string $reviewer_email) : int {
            return $this->Tables->PaperReview->insert([
                'paper_id' => $paper_id,
                'paper_version' => $paper_version,
                'reviewer_email' => $reviewer_email,
                'review_status' => self::review_status_before_review,
                'review_result' => self::review_result_unknown,
                'review_comment' => ''
            ]);
        }

        /**
         * 某篇paper的指定reviewer是否存在
         * @param int $paper_id
         * @param int $paper_version
         * @param string $reviewer_email
         * @return bool
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function reviewer_exists(int $paper_id, int $paper_version, string $reviewer_email) : bool {
            return $this->Tables->PaperReview->exist(['paper_id' => $paper_id, 'paper_version' => $paper_version, 'reviewer_email' => $reviewer_email]);
        }

        /**
         * 从指定的文章中删除指定的reviewer
         * @param int $paper_id
         * @param string $reviewer_email
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function delete_reviewer_from_paper(int $paper_id, int $paper_version, string $reviewer_email) : void {
            $this->Tables->PaperReview->delete(['paper_id' => $paper_id, 'paper_version' => $paper_version, 'reviewer_email' => $reviewer_email]);
        }

        /**
         * 保存review的记录
         * @param int $paper_id
         * @param string $reviewer_email
         * @param string $review_result
         * @param string $review_comment
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function save_review(int $paper_id, int $paper_version, string $reviewer_email, string $review_result, string $review_comment) : void {
            $this->Tables->PaperReview->set(['paper_id' => $paper_id, 'paper_version' => $paper_version, 'reviewer_email' => $reviewer_email], ['review_result' => $review_result, 'review_comment' => $review_comment]);
        }

        /**
         * 提交review
         * @param int $paper_id
         * @param string $reviewer_email
         * @param string $review_result
         * @param string $review_comment
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function submit_review(int $paper_id, int $paper_version, string $reviewer_email, string $review_result, string $review_comment) : void {
            $this->Tables->PaperReview->set(['paper_id' => $paper_id, 'paper_version' => $paper_version, 'reviewer_email' => $reviewer_email], ['review_result' => $review_result, 'review_comment' => $review_comment, 'review_status' => self::review_status_finished_review]);
        }

        /**
         * 某个审稿人进入评审环节
         * @param int $paper_id
         * @param string $reviewer_email
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function enter_review(int $paper_id, int $paper_version, string $reviewer_email) : void {
            $this->Tables->PaperReview->set(['paper_id' => $paper_id, 'paper_version' => $paper_version, 'reviewer_email' => $reviewer_email], ['review_result' => self::review_result_unknown, 'review_status' => self::review_status_under_review]);
        }

        /**
         * 编辑开始审稿
         * @param int $paper_id
         * @param int $paper_version
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function editor_begin_review(int $paper_id, int $paper_version) : void {
            $this->Tables->Papers->set(
                ['paper_id' => strval($paper_id), 'paper_version' => $paper_version],
                ['paper_status' => \myConf\Models\Paper::paper_status_under_review]
            );
        }

        /**
         * 编辑结束审稿
         * @param int $paper_id
         * @param int $paper_version
         * @param int $paper_status
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbCompositeKeysException
         */
        public function editor_finished_review(int $paper_id, int $paper_version, int $paper_status) : void {
            $this->Tables->Papers->set(
                ['paper_logic_id' => strval($paper_id), 'paper_version' => $paper_version],
                ['paper_status' => $paper_status]
            );
        }

        /**
         * 得到某个会议中某个审稿人的所有任务
         * @param string $reviewer_email
         * @param int $conference_id
         * @return array
         */
        public function get_reviewer_conference_tasks(string $reviewer_email,
                                                      int $conference_id = 0) :
        array {
            $table_review = \myConf\Libraries\DbHelper::make_table('paper_review');
            $table_paper = \myConf\Libraries\DbHelper::make_table('papers');
            //use joint queries
            $sql = "SELECT $table_review.review_status, $table_paper.* FROM $table_review, $table_paper WHERE $table_review.reviewer_email = '%s' AND $table_paper.paper_logic_id = $table_review.paper_id AND $table_review.paper_version = $table_paper.paper_version" . ($conference_id === 0 ? "" : " AND $table_paper.conference_id = %d");

            if ($conference_id === 0) {
                $results = \myConf\Libraries\DbHelper::fetch_all_raw(sprintf($sql, $reviewer_email));
            } else {
                $results = \myConf\Libraries\DbHelper::fetch_all_raw(sprintf($sql, $reviewer_email, $conference_id));
            }

            foreach($results as &$record) {
                foreach ($record as $key => $val) {
                    if (strpos($key, '.') !== FALSE) {
                        explode('.', $key)[1] = $val;
                        unset($record[$key]);
                    }
                }
            }
            //\myConf\Libraries\Debugger::print_and_stop($results, __FILE__, __LINE__);
            return $results;
        }
    }
