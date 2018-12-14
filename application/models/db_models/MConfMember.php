<?php

/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/11/11
 * Time: 13:37
 */
class mConfMember extends CF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'conference_members';
    }

    /**
     * @param $conference_id
     * @param int $start
     * @param int $limit
     * @param string $user_name_restrict
     * @param string $user_email_restrict
     * @param array $role_restrict
     * @return array
     */
    public function get_conference_members($conference_id, $start = 0, $limit = 0, $user_name_restrict = '', $user_email_restrict = '', $role_restrict = array())
    {
        return $this->_fetch_all(
            array('conference_id' => $conference_id),
            '',
            '',
            $start,
            $limit
        );
    }

    /**
     * @param $conference_id
     * @return int
     */
    public function get_conference_members_count($conference_id)
    {
        $sql_result = $this->_fetch_first_raw('SELECT COUNT(1) FROM ' . $this->_table() . ' WHERE conference_id = ' . $conference_id);
        return intval($sql_result['COUNT(1)']);
    }

    public function get_conference_members_by_role()
    {

    }

    public function get_user_conferences($user_id, $start = 0, $limit = 10)
    {
        return $this->_fetch_all(
            array('user_id' => $user_id),
            '',
            '',
            $start,
            $limit
        );
    }

    /**
     * 判断一个用户是否加入了这个会议。
     * @param $user_id
     * @param $conference_id
     * @return bool
     */
    public function user_joint_in_conference($user_id, $conference_id)
    {
        return $this->_exists(array('user_id' => $user_id, 'conference_id' => $conference_id));
    }

    /**
     * @param $user_id
     * @param $conference_id
     * @param string $role
     */
    public function add_member_to_conference($user_id, $conference_id)
    {
        $this->db->insert(
            $this->_table(),
            array(
                'user_id' => $user_id,
                'conference_id' => $conference_id,
                'user_role' => 'scholar'
            )
        );
    }

    /**
     * @param $user_id
     * @param $conference_id
     */
    public function remove_member_from_conference($user_id, $conference_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('conference_id', $conference_id);
        $this->db->delete($this->_table());
    }

    /**
     * @param $user_id
     * @param $conference_id
     * @param $role
     */
    public function add_role_to_member($user_id, $conference_id, $role)
    {
        $roles = $this->get_member_roles($user_id, $conference_id);
        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $this->db->where('user_id', $user_id);
            $this->db->update($this->_table(), array('user_role' => implode(',', $roles)));
        }
    }

    /**
     * @param $user_id
     * @param $conference_id
     * @return array
     */
    public function get_member_roles($user_id, $conference_id)
    {
        $user = $this->_fetch_first(array('user_id' => $user_id, 'conference_id' => $conference_id));
        return explode(',', $user['user_role']);
    }

    /**
     * @param $user_id
     * @param $conference_id
     * @param $role
     */
    public function delete_role_from_member($user_id, $conference_id, $role)
    {
        $roles = $this->get_member_roles($user_id, $conference_id);
        $roles_new = array();
        foreach ($roles as &$iter_role) {
            if ($iter_role == $role) {
                continue;
            }
            $roles_new [] = $iter_role;
        }
        $this->db->where('user_id', $user_id);
        $this->db->update($this->_table(), array('user_role' => implode(',', $roles_new)));
    }

    /**
     * @param $user_id
     * @param $conference_id
     * @param $role
     * @return bool
     */
    public function member_is_role($user_id, $conference_id, $role)
    {
        return in_array($role, $this->get_member_roles($user_id, $conference_id));
    }

}