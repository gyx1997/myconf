<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/28
 * Time: 10:38
 */

class mUser extends CF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'users';
    }

    /**
     * @param int $user_id
     * @param array $data
     */
    public function update_extra($user_id, $data)
    {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get($this->_table());
        $result = $query->row_array();
        $extra = unserialize($result['user_extra']);
        foreach ($data as $key => $value) {
            $extra[$key] = $value;
        }
        $this->db->where('user_id', $user_id);
        $this->db->update($this->_table(), array('user_extra' => serialize($extra)));
    }

    public function add_user($username, $password, $email, $salt)
    {
        $this->db->insert(
            $this->_table(),
            array(
                'user_name' => $username,
                'user_email' => $email,
                'user_password' => $password,
                'password_salt' => $salt,
                'is_frozen' => 1,
                'user_role' => 'user',
                'user_extra' => serialize(
                    array(
                        'avatar' => '',
                        'organization' => '',
                    )
                ),
            )
        );
        return $this->db->insert_id();
    }

    public function activate_user($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->update(
            $this->_table(),
            array('is_frozen' => 0)
        );
    }

    /**
     * @deprecated
     * @param $user_id
     * @param $role
     */
    public function add_user_role($user_id, $role)
    {
        $roles = $this->_get_user_roles($user_id);
        if (!in_array($role, $roles)) {
            $roles['user_role'][] = $role;
            $this->db->where('user_id', $user_id);
            $this->db->update($this->_table(), array('user_role' => implode(',', $roles)));
        }
    }

    /**
     * @param $user_id
     * @return array
     */
    private function _get_user_roles($user_id)
    {
        $user = $this->get_user_by_user_id($user_id);
        return explode(',', $user['user_role']);
    }

    /**
     * @param $user_id
     * @return array
     */
    public function get_user_by_user_id($user_id)
    {
        return $this->_get_single_user('user_id', $user_id);
    }

    private function _get_single_user($field, $value)
    {
        $this->db->where($field, $value);
        $query = $this->db->get($this->_table());
        $result = $query->row_array();
        if (empty($result)) {
            return array();
        } else {
            $result['user_role'] = explode(',', $result['user_role']);
            $tmp = unserialize($result['user_extra']);
            foreach ($tmp as $k => $v) {
                $result[$k] = $v;
            }
            $result['user_extra'] = NULL;
            return $result;
        }
    }

    /**
     * @param $user_id
     * @param $role
     */
    public function delete_user_role($user_id, $role)
    {
        $roles = $this->_get_user_roles($user_id);
        foreach ($roles as &$iter_role) {
            if ($iter_role == $role) {
                $iter_role = NULL;
            }
        }
        $this->db->where('user_id', $user_id);
        $this->db->update($this->_table(), array('user_role' => implode(',', $roles)));
    }

    /**
     * 判断某一用户是否具有某一权限
     * @param $user_id
     * @param $role
     * @return bool
     */
    public function in_roles($user_id, $role)
    {
        return in_array($role, $this->_get_user_roles($user_id));
    }

    /**
     * @param string $username
     * @param string $role
     * @return mixed
     */
    public function find_user_by_name_and_role($username = NULL, $role = NULL)
    {
        $order_by_clause = ' ORDER BY user_id DESC';
        if (($username === NULL && $role === NULL) || ($username == '' && $role == 'none')) {
            $sql_str = 'SELECT * FROM ' . $this->_table() . $order_by_clause;
        } else {
            $where_str = array();
            if ($username !== NULL && $username != '') {
                $where_str [] = ' `user_name` LIKE "%' . $username . '%" ';
            }
            if ($role !== NULL && $role != 'none') {
                $where_str [] = ' `user_role` = "' . $role . '" ';
            }
            $sql_str = 'SELECT * FROM ' . $this->_table() . ' WHERE ' . implode('AND', $where_str) . $order_by_clause;
        }
        $query_result = $this->db->query($sql_str);
        return $query_result->result_array();
    }

    public function get_user_by_username($username)
    {
        return $this->_get_single_user('user_name', $username);
    }

    public function get_user_by_email($email)
    {
        return $this->_get_single_user('user_email', $email);
    }

    public function exists_username($username)
    {
        return $this->_exists_single_user('user_name', $username);
    }

    private function _exists_single_user($field, $value)
    {
        return $this->_exists(array($field => $value));
    }

    public function exists_email($email)
    {
        return $this->_exists_single_user('user_email', $email);
    }
}