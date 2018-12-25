<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 16:59
 */

namespace myConf\Models;


class User extends \myConf\BaseModel
{
    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $user_id
     * @param string $new_avatar
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function set_avatar(int $user_id, string $new_avatar) : void {
        $this->Tables->Users->set(strval($user_id), array('user_avatar' => $new_avatar));
    }

    /**
     * @param int $user_id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function get_by_id(int $user_id) : array {
        return $this->Tables->Users->get(strval($user_id));
    }

    /**
     * @param int $user_id
     * @param string $password
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function set_password(int $user_id, string $password) : void {
        $salt = $this->_generate_password_salt();
        $this->Tables->Users->set($user_id, array('user_password' => md5($password . $salt), 'password_salt' => $salt));
    }

    /**
     * @param string $email
     * @return bool
     */
    public function exist_by_email(string $email) : bool {
        return $this->Tables->Users->exist_by_email($email);
    }

    /**
     * @param string $email
     * @return array
     */
    public function get_by_email(string $email) : array {
        return $this->Tables->Users->get_by_email($email);
    }

    /**
     * @param string $username
     * @return bool
     */
    public function exist_by_username(string $username) : bool {
        return $this->Tables->Users->exist_by_username($username);
    }

    /**
     * @param string $username
     * @return array
     */
    public function get_by_username(string $username) : array {
        return $this->Tables->Users->get_by_username($username);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @return int
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function create_new(string $username, string $password, string $email) : int {
        $salt = $this->_generate_password_salt();
        \myConf\Libraries\DbHelper::begin_trans();
        $user_id = $this->Tables->Users->create($username, md5($password . $salt), $email, $salt);
        $this->Tables->Scholars->insert(array('scholar_email' => $email));
        \myConf\Libraries\DbHelper::end_trans();
        return $user_id;
    }

    /**
     * @param int $user_id
     */
    public function activate(int $user_id) : void
    {
        $this->Tables->Users->activate($user_id);
    }

    /**
     * 得到相关联的Scholar信息
     * @param int $user_id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function get_assigned_scholar(int $user_id) : array {
        $user = $this->Tables->Users->get(strval($user_id));
        return $this->Tables->Scholars->get_by_email($user['user_email']);
    }

    /**
     * 生成密码用的盐
     * @return string
     */
    private function _generate_password_salt() : string
    {
        return md5(uniqid() . strval(time()));
    }
}