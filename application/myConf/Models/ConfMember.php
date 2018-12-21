<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/16
 * Time: 0:16
 */

namespace myConf\Models;


class ConfMember extends \myConf\BaseModel
{
    /**
     * ConfMember constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'conference_members';
        //dummy use
        $this->_pk = 'id';
    }

    /**
     * 获取会议的成员。获取数据库所有满足条件的记录
     * @param $conference_id
     * @return array
     */
    public function get_conference_members(int $conference_id): array
    {
        //NOTE:使用连表查询，写死了表明，后面需要注意
        //NOTE:过滤角色信息，请在上一层处理，因为返回的是数组展开的字符串
        $user_table = $this->make_table('users');
        $this_table = $this->_table();
        $sql = "SELECT $this_table.user_id, $this_table.conference_id, $this_table.user_role, $user_table.user_name, $user_table.user_email FROM $this_table INNER JOIN $user_table ON $user_table.user_id = $this_table.user_id WHERE $user_table.user_id = $this_table.user_id AND $this_table.conference_id = " . strval($conference_id);
        $data = $this->_fetch_all_raw($sql);
        foreach ($data as &$item) {
            $item['user_roles'] = explode(',', $item['user_role']);
        }
        return $data;
    }

    /**
     * 得到某个会议的参与人数
     * @param int $conference_id
     * @return int
     */
    public function get_conference_members_count(int $conference_id): int
    {
        $sql_result = $this->_fetch_first_raw('SELECT COUNT(1) FROM ' . $this->_table() . ' WHERE conference_id = ' . strval($conference_id));
        return intval($sql_result['COUNT(1)']);
    }

    /**
     * 得到用户参与的会议列表
     * @param int $user_id
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function get_user_conferences(int $user_id, int $start = 0, int $limit = 10): array
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
    public function user_joint_in_conference(int $user_id, int $conference_id): bool
    {
        return $this->_exists(array('user_id' => $user_id, 'conference_id' => $conference_id));
    }

    /**
     * 将用户加入会议
     * @param int $user_id
     * @param int $conference_id
     * @return int
     */
    public function add_member_to_conference(int $user_id, int $conference_id): int
    {
        $this->db->insert(
            $this->_table(),
            array(
                'user_id' => $user_id,
                'conference_id' => $conference_id,
                'user_role' => 'scholar'
            )
        );
        return $this->db->insert_id();
    }

    /**
     * 将用户移出会议
     * @param int $user_id
     * @param int $conference_id
     */
    public function remove_member_from_conference(int $user_id, int $conference_id): void
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('conference_id', $conference_id);
        $this->db->delete($this->_table());
    }

    /**
     * 将用户增加角色
     * @param int $user_id
     * @param int $conference_id
     * @param string $role
     */
    public function add_role_to_member(int $user_id, int $conference_id, string $role): void
    {
        $roles = $this->get_member_roles($user_id, $conference_id);
        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $this->db->where('user_id', $user_id);
            $this->db->update($this->_table(), array('user_role' => implode(',', $roles)));
        }
    }

    /**
     * 得到用户角色
     * @param int $user_id
     * @param int $conference_id
     * @return array
     */
    public function get_member_roles(int $user_id, int $conference_id): array
    {
        $user = $this->_fetch_first(array('user_id' => $user_id, 'conference_id' => $conference_id));
        return explode(',', $user['user_role']);
    }

    /**
     * 从参会者身上移除角色
     * @param int $user_id
     * @param int $conference_id
     * @param string $role
     */
    public function delete_role_from_member(int $user_id, int $conference_id, string $role): void
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
     * 判断用户是不是某一角色
     * @param $user_id
     * @param $conference_id
     * @param $role
     * @return bool
     */
    public function member_is_role(int $user_id, int $conference_id, string $role): bool
    {
        return in_array($role, $this->get_member_roles($user_id, $conference_id));
    }

    /**
     * dummy method
     * @deprecated
     * @param string $pk_val
     * @return array
     */
    public function get(string $pk_val): array
    {
        return parent::get($pk_val);
    }
}