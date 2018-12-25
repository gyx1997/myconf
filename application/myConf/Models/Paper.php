<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/24
     * Time: 13:48
     */

    namespace myConf\Models;

    use myConf\Libraries\DbHelper;

    /**
     * Class Paper
     * @package myConf\Models
     * @author _g63<522975334@qq.com>
     * @version 2019.1
     */
    class Paper extends \myConf\BaseModel {

        public const paper_status_submitted = 0;
        public const paper_status_under_review = 1;
        public const paper_status_passed = 2;
        public const paper_status_rejected = 3;
        public const paper_need_modify = 4;
        public const paper_saved = -1;

        /**
         * Paper constructor.
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * @param int $user_id
         * @param int $conference_id
         * @param array $authors
         * @param array $pdf_file_info
         * @param array $copyright_file_info
         * @param string $paper_type
         * @param string $suggested_session
         * @param string $title
         * @param string $abstract
         * @param int $paper_status
         * @return int
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public function add(int $user_id, int $conference_id, array $authors, array $pdf_file_info, array $copyright_file_info, string $paper_type, string $suggested_session, string $title, string $abstract, int $paper_status = 0) : int {
            DbHelper::begin_trans();
            $paper_id = $this->Tables->Papers->insert([
                'user_id' => $user_id,
                'conference_id' => $conference_id,
                'pdf_attachment_id' => 0,
                'copyright_attachment_id' => 0,
                'paper_submit_time' => time(),
                'paper_status' => $paper_status,
                'paper_type' => $paper_type,
                'paper_suggested_session' => $suggested_session,
                'paper_title' => $title,
                'paper_abstract' => $abstract,
            ]);
            $pdf_aid = $this->Tables->Attachments->insert([
                'attachment_file_name' => $pdf_file_info['full_name'],
                'attachment_is_image' => 0,
                'attachment_file_size' => $pdf_file_info['size'],
                'attachment_original_name' => $pdf_file_info['original_name'],
                'attachment_image_height' => 0,
                'attachment_image_width' => 0,
                'attachment_tag_id' => 'paper',
                'attachment_tag_type' => $this->Tables->Attachments::tag_type_paper,
                'attachment_used' => 1,
                'attachment_filename_hash' => crc32($pdf_file_info['full_name']),
            ]);
            $copyright_aid = $this->Tables->Attachments->insert([
                'attachment_file_name' => $copyright_file_info['full_name'],
                'attachment_is_image' => 0,
                'attachment_file_size' => $copyright_file_info['size'],
                'attachment_original_name' => $copyright_file_info['original_name'],
                'attachment_image_height' => 0,
                'attachment_image_width' => 0,
                'attachment_tag_id' => 'paper',
                'attachment_tag_type' => $this->Tables->Attachments::tag_type_paper,
                'attachment_used' => 1,
                'attachment_filename_hash' => crc32($copyright_file_info['full_name']),
            ]);
            //先准备数据
            $data_authors_array = [];
            $display_order = 0;
            foreach ($authors as $author) {
                $data_authors_array [] = [
                    'paper_id' => $paper_id,
                    'author_email' => $author['email'],
                    'author_display_order' => $display_order,
                    'author_address' => $author['address'],
                    'author_institution' => $author['institution'],
                    'author_department' => $author['department'],
                    'author_first_name' => $author['first_name'],
                    'author_last_name' => $author['last_name'],
                    'author_chn_full_name' => $author['chn_full_name'],
                    'author_prefix' => $author['prefix'],
                ];
                $display_order++;
            }
            $this->Tables->PaperAuthors->insert_array($data_authors_array);
            $this->Tables->Papers->set(strval($paper_id), [
                'pdf_attachment_id' => $pdf_aid,
                'copyright_attachment_id' => $copyright_aid,
            ]);
            DbHelper::end_trans();
            return $paper_id;
        }

        public function get_by_conference_id_and_user_id(int $conference_id, int $user_id) : array {
            $papers = $this->Tables->Papers->fetch_all(['conference_id' => $conference_id, 'user_id' => $user_id]);
            foreach ($papers as &$paper) {
                $authors = $this->Tables->PaperAuthors->fetch_all(['paper_id' => $paper['paper_id']]);
                $paper['authors'] = $authors;
            }
            return $papers;
        }
    }