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
class Paper extends \myConf\BaseModel
{

    //下面是paper_status的取值列表
    public const paper_status_submitted = 0;
    public const paper_status_under_review = 1;
    public const paper_status_passed = 2;
    public const paper_status_rejected = 3;
    public const paper_status_revision = 4;
    public const paper_saved = -1;
    //下面是paper session的取值列表
    public const paper_session_type_internal = 0;
    public const paper_session_type_custom = 1;
    //tinyint 类型，2~255可供自定义使用
    public const paper_session_type_student_paper = 2;

    /**
     * Paper constructor.
     */
    public function __construct()
    {
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
     * @param int $status
     * @param string $custom_suggested_session
     * @return int
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function add(int $user_id, int $conference_id, array $authors, array $pdf_file_info, array $copyright_file_info, string $paper_type, string $suggested_session, string $title, string $abstract, int $status, string $custom_suggested_session = ''): int
    {
        //放在事务外面，读取不加写锁
        $session = $this->Tables->PaperSessions->get(strval(intval($suggested_session)));
        $session_display_order_max = $this->Tables->PaperSessions->get_conference_sessions_max_display_order($conference_id);
        $paper_logic_id = $this->Tables->Papers->get_new_paper_logic_id();
        $paper_version = 1;
        DbHelper::begin_trans();
        //如果不存在的话，写入session信息
        if (empty($session)) {
            $session_id = $this->Tables->PaperSessions->insert([
                'session_conference_id' => $conference_id,
                'session_text' => $custom_suggested_session,
                'session_type' => self::paper_session_type_custom,
                'session_display_order' => $session_display_order_max + 1
            ]);
            $suggested_session = $session_id;
        }
        //如果上传了paper的pdf文件，那么将其写入Attachment表，反之，attachment_id记为0
        $pdf_aid = empty($pdf_file_info) ? 0 : $this->Tables->Attachments->insert([
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
        //如果上传了copyright文件，那么将其写入Attachment表
        $copyright_aid = empty($copyright_file_info) ? 0 : $this->Tables->Attachments->insert([
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
        //插入paper信息
        $paper_id = $this->Tables->Papers->insert([
            'paper_logic_id' => $paper_logic_id,
            'paper_version' => $paper_version,
            'user_id' => $user_id,
            'conference_id' => $conference_id,
            'pdf_attachment_id' => $pdf_aid,
            'copyright_attachment_id' => $copyright_aid,
            'paper_submit_time' => time(),
            'paper_status' => self::paper_saved,
            'paper_type' => $paper_type,
            'paper_suggested_session' => $suggested_session,
            'paper_title' => $title,
            'paper_abstract' => $abstract,
        ]);
        //插入作者信息（如果填写了的话）
        !empty($authors) && $this->Tables->PaperAuthors->insert_array($this->_parse_authors($paper_id, $authors));
        DbHelper::end_trans();
        //原子性操作，修改attachment_tag_id，即attachment的关联的表的id
        $this->Tables->Attachments->set($pdf_aid, ['attachment_tag_id' => $paper_id]);
        $this->Tables->Attachments->set($copyright_aid, ['attachment_tag_id' => $paper_id]);
        return $paper_id;
    }

    /**
     * 根据会议ID和用户ID获取当前某个会议某个用户的所有文章
     * @param int $conference_id
     * @param int $user_id
     * @return array
     */
    public function get_by_conference_id_and_user_id(int $conference_id, int $user_id): array
    {
        $papers = $this->Tables->Papers->fetch_all(['conference_id' => $conference_id, 'user_id' => $user_id]);
        foreach ($papers as &$paper) {
            $authors = $this->Tables->PaperAuthors->fetch_all(['paper_id' => $paper['paper_id']]);
            $paper['authors'] = $authors;
        }
        return $papers;
    }

    /**
     * 根据文章的逻辑编号和版本号得到文章内容
     * @param int $paper_logic_id
     * @param int $paper_version
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     */
    public function get_paper(int $paper_logic_id, int $paper_version): array
    {
        $paper_base_data = $this->Tables->Papers->get(['paper_logic_id' => $paper_logic_id, 'paper_version' => $paper_version]);

        if (empty($paper_base_data)) {
            return [];
        }
        $paper_authors = $this->Tables->PaperAuthors->fetch_all(['paper_id' => $paper_base_data['paper_id']]);
        $paper_base_data['authors'] = $paper_authors;
        $paper_base_data['content_attach_info'] = $this->Tables->Attachments->get(strval($paper_base_data['pdf_attachment_id']));
        $paper_base_data['copyright_attach_info'] = $this->Tables->Attachments->get(strval($paper_base_data['copyright_attachment_id']));
        return $paper_base_data;
    }

    /**
     * @param int $paper_logic_id
     * @param int $paper_version
     * @param array|null $authors
     * @param array|null $pdf_file_info
     * @param array|null $copyright_file_info
     * @param string|null $paper_type
     * @param string|null $suggested_session
     * @param string|null $title
     * @param string|null $abstract
     * @param string|null $custom_suggested_session
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function update_paper_by_id(int $paper_logic_id, int $paper_version, array $authors = null, array $pdf_file_info = null, array $copyright_file_info = null, string $paper_type = null, string $suggested_session = null, string $title = null, string $abstract = null, string $custom_suggested_session = null): void
    {
        //先获取旧的信息，进行比对，如果有出入则进行修改
        $old_data = $this->get_paper($paper_logic_id, $paper_version);
        $base_data_to_update = [];
        //获取session信息(如果必要的话)
        if (isset($suggested_session)) {
            $session = $this->Tables->PaperSessions->get(strval(intval($suggested_session)));
            $session_display_order_max = $this->Tables->PaperSessions->get_conference_sessions_max_display_order($old_data['conference_id']);
        }
        //下面开始事务修改信息
        DbHelper::begin_trans();
        //paper本身的pdf文件是不是上传新的了
        if (isset($pdf_file_info)) {
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
                //'attachment_filename_hash' => crc32($pdf_file_info['full_name']),
            ]);
            $this->Tables->Attachments->set_used_status($old_data['pdf_attachment_id'], false);
            $base_data_to_update['pdf_attachment_id'] = $pdf_aid;
        }
        //copyright是不是更新了
        if (isset($copyright_file_info)) {
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
            ]);
            $this->Tables->Attachments->set_used_status($old_data['copyright_attachment_id'], false);
            $base_data_to_update['copyright_attachment_id'] = $copyright_aid;
        }

        //如果不存在的话，写入session信息
        if (empty($session)) {
            $session_id = $this->Tables->PaperSessions->insert([
                'session_conference_id' => $old_data['conference_id'],
                'session_text' => $custom_suggested_session,
                'session_type' => self::paper_session_type_custom,
                'session_display_order' => $session_display_order_max + 1,
            ]);
            $suggested_session = $session_id;
        }

        //下面开始处理作者信息
        if (isset($authors)) {
            //先清除旧的信息
            foreach ($old_data['authors'] as $current_author) {
                $this->Tables->PaperAuthors->delete($current_author['author_id']);
            }
            //再插入新的数据
            $this->Tables->PaperAuthors->insert_array($this->_parse_authors($paper_logic_id, $authors));
        }
        //其他的一些信息
        isset($paper_type) && $base_data_to_update['paper_type'] = $paper_type;
        isset($suggested_session) && $base_data_to_update['paper_suggested_session'] = $suggested_session;
        isset($title) && $base_data_to_update['paper_title'] = $title;
        isset($abstract) && $base_data_to_update['paper_abstract'] = $abstract;
        $this->Tables->Papers->set([
                'paper_logic_id' => $paper_logic_id,
                'paper_version' => $paper_version
            ], $base_data_to_update
        );
        DbHelper::end_trans();
    }

    public function add_new_version(int $paper_logic_id, int $current_version, int $user_id, int $conference_id, array $authors, array $pdf_file_info, array $copyright_file_info, string $paper_type, string $suggested_session, string $title, string $abstract, int $paper_status = 0, string $custom_suggested_session = '') : int {

    }

    /**
     * @param int $paper_logic_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function delete_paper(int $paper_logic_id, int $paper_version): void
    {
        $paper = $this->Tables->Papers->get(['paper_logic_id' => $paper_logic_id, 'paper_version' => $paper_version]);
        $authors = $this->Tables->PaperAuthors->fetch_all(['paper_id' => $paper_logic_id]);
        $paper_session = $this->Tables->PaperSessions->get($paper['paper_suggested_session']);
        DbHelper::begin_trans();
        //如果session是自己添加的，那么需要删除之
        if ($paper_session['session_type'] === self::paper_session_type_custom) {
            $this->Tables->PaperSessions->delete($paper_session['session_id']);
        }
        //删除所有作者信息
        foreach($authors as $author) {
            $this->Tables->PaperAuthors->delete($author['author_id']);
        }
        //删除paper信息
        $this->Tables->Papers->delete(['paper_logic_id' => $paper_logic_id, 'paper_version' => $paper_version]);
        //删除对应的附件信息
        $this->Tables->Attachments->delete($paper['pdf_attachment_id']);
        $this->Tables->Attachments->delete($paper['copyright_attachment_id']);
        DbHelper::end_trans();
    }

    /**
     * 从输入数组转换到数据库表数组
     * @param int $paper_id
     * @param array $authors
     * @return array
     */
    private function _parse_authors(int $paper_id, array $authors): array
    {
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
        return $data_authors_array;
    }
}