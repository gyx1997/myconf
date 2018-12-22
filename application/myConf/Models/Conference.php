<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 22:57
 */

namespace myConf\Models;

/**
 * Class Conference
 * @package myConf\Models
 * @author _g63<522975334@qq.com>
 * @version 2019.1
 */
class Conference extends \myConf\BaseModel
{

    public $conference_status_mapping = array(
        'moderated' => 0,
        'normal' => 1
    );

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'conferences';
        $this->_pk = 'conference_id';
    }

    /**
     * @param int $conference_id
     * @param $conference_name
     * @param $conference_start_time
     * @param string $conference_banner
     * @param string $conference_qr_code
     * @param string $conference_host
     * @param bool $use_paper_submission
     * @param int $paper_submission_deadline
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function update_conference(int $conference_id, $conference_name = '', $conference_start_time = 0,
                                      $conference_banner = '',
                                      $conference_qr_code = '', $conference_host = '', $use_paper_submission = true, $paper_submission_deadline = 0)
    {
        $this->Tables->Conferences->set(strval($conference_id), array(
                'conference_name' => $conference_name,
                'conference_start_time' => $conference_start_time,
                'conference_banner_image' => $conference_banner,
                'conference_qr_code' => $conference_qr_code,
                'conference_host' => $conference_host,
                'conference_use_paper_submit' => $use_paper_submission,
                'conference_paper_submit_end' => $paper_submission_deadline,
            )
        );
    }

    /**
     * 通过url获取会议信息
     * @param string $url
     * @return array
     */
    public function get_by_url(string $url) : array
    {
        return $this->Tables->Conferences->fetch_first(array('conference_url' => $url));
    }

    /**
     * 通过id获取会议信息
     * @param int $conference_id
     * @return array
     */
    public function get_by_id(int $conference_id) : array {
        return $this->Tables->Conferences->get(strval($conference_id));
    }

    /**
     * 判断用户是否加入了某个会议
     * @param int $conference_id
     * @param int $user_id
     * @return bool
     */
    public function user_joint_in(int $conference_id, int $user_id) : bool {
        return $this->Tables->ConferenceMembers->user_joint_in_conference($user_id, $conference_id);
    }

    /**
     * 得到用户在某个会议的角色
     * @param int $conference_id
     * @param int $user_id
     * @return array
     */
    public function user_roles(int $conference_id, int $user_id) : array {
        return $this->Tables->ConferenceMembers->get_user_roles_in_conference($user_id, $conference_id);
    }

    /**
     * 得到Home页面的所有栏目列表
     * @param int $conference_id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function categories(int $conference_id) : array {
        $result = array();
        $ids = $this->Tables->Categories->get_ids_by_conference($conference_id);
        foreach ($ids as $id) {
            $result [] = $this->Tables->Categories->get(strval($id));
        }
        return $result;
    }

    /**
     * 得到某个会议的第一个栏目
     * @param int $conference_id
     * @return array
     */
    public function first_category(int $conference_id) : array {
        return $this->Tables->Categories->fetch_first(array('conference_id' => $conference_id), 'category_display_order', 'ASC');
    }

    /**
     * 返回当前的会议是否存在
     * @param int $conference_id
     * @return bool
     */
    public function exist(int $conference_id) : bool {
        return $this->Tables->Conferences->exist(strval($conference_id));
    }
}