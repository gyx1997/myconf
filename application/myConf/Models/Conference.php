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
     * @param string $conference_name
     * @param string $conference_start_time
     * @param string $conference_banner
     * @param string $conference_qr_code
     * @param string $conference_host
     */
    public function update_conference($conference_id,
                                      $conference_name,
                                      $conference_start_time,
                                      $conference_banner = '',
                                      $conference_qr_code = '',
                                      $conference_host = '')
    {
        $this->db->where('conference_id', $conference_id);
        $extra_data = array();
        $this->db->update(
            $this->_table(),
            array(
                'conference_name' => $conference_name,
                'conference_start_time' => $conference_start_time,
                'conference_extra' => serialize(
                    array(
                        'banner_image' => $conference_banner,
                        'qr_code' => $conference_qr_code,
                        'host' => $conference_host
                    )
                )
            )
        );
    }

    /**
     * 通过url获取会议信息
     * @param $url
     * @return array
     */
    public function get_by_url($url) : array
    {
        $result_array = $this->Tables->Conferences->fetch_first(array('conference_url' => $url));
        if (empty($result_array)) {
            return array();
        }
        return $result_array;
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
     */
    public function category_list(int $conference_id) : array {
        return $this->Tables->Categories->fetch_all(array('conference_id' => $conference_id), 'category_display_order', 'ASC');
    }

    /**
     * 得到某个会议的第一个栏目
     * @param int $conference_id
     * @return array
     */
    public function first_category(int $conference_id) : array {
        return $this->Tables->Categories->fetch_first(array('conference_id' => $conference_id), 'category_display_order', 'ASC');
    }
}