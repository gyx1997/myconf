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
         * @return int
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbTransactionException
         * @throws \myConf\Exceptions\FileUploadException
         */
        public function submit_new(int $user_id, int $conference_id, string $title, string $abstract, array $authors, string $pdf_field, string $copyright_field, string $type, string $suggested_session) : int {
            $pdf_result = \myConf\Libraries\Attach::parse_attach($pdf_field);
            $copyright_result = \myConf\Libraries\Attach::parse_attach($copyright_field);
            return $this->Models->Paper->add($user_id, $conference_id, $authors, $pdf_result, $copyright_result, $type, $suggested_session, $title, $abstract, $this->Models->Paper::paper_status_submitted);
        }

        /**
         * @param int $user_id
         * @param int $conference_id
         * @return array
         */
        public function get_user_conference_papers(int $user_id, int $conference_id) : array {
            return $this->Models->Paper->get_by_conference_id_and_user_id($conference_id, $user_id);
        }

    }