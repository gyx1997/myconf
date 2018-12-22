<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 17:05
 */

namespace myConf\Services;

class Account extends \myConf\BaseService
{
    /**
     * Account constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 用户登录逻辑
     * @param string $entry
     * @param string $password
     * @return array
     * @throws \myConf\Exceptions\UserNotExistsException
     * @throws \myConf\Exceptions\UserPasswordException
     */
    public function login(string $entry, string $password): array
    {
        if ($this->Models->User->exist_by_username($entry)) {
            $user = $this->Models->User->get_by_username($entry);
        } else if ($this->Models->User->exist_by_email($entry)) {
            $user = $this->Models->User->get_by_email($entry);
        } else {
            throw new \myConf\Exceptions\UserNotExistsException();
        }
        if ($user['user_password'] !== md5($password . $user['password_salt'])) {
            throw new \myConf\Exceptions\UserPasswordException();
        }
        return $user;
    }

    /**
     * 注册逻辑
     * @param string $email
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Exception
     * @throws \sAccount\EmailAlreadyExistsException
     * @throws \sAccount\UsernameAlreadyExistsException
     */
    public function register(string $email, string $username, string $password): array
    {
        if ($email === '' || $this->Models->User->exist_by_email($email)) {
            throw new \sAccount\EmailAlreadyExistsException();
        }
        if ($username === '' || $this->Models->User->exist_by_username($username)) {
            throw new \sAccount\UsernameAlreadyExistsException();
        }
        //新建用户事务开始
        $this->Models->trans_block_begin();
        $user_id = $this->Models->User->create_new($username, $password, $email);
        $this->Models->Scholar->create_new($email);
        $this->Models->trans_block_end();
        //新建用户事务结束
        return $this->Models->User->get_by_id($user_id);
    }

    /**
     * 发送验证邮件业务逻辑
     * @param string $email
     * @param string $hash_key
     * @throws \myConf\Exceptions\UserNotExistsException
     */
    public function send_verify_email(string $email, string $hash_key): void
    {
        if (!$this->Models->User->exist_by_email($email)) {
            throw new \myConf\Exceptions\UserNotExistsException();
        }
        $CI = &get_instance();
        $CI->email->from('csqrwc@126.com', 'myConf Password Reset');
        $CI->email->to($email);
        $CI->email->subject('Password Reset');
        $CI->email->message(
            '
            <h1>Email Verification From myConf.cn</h1>
            <p>Copy the text below to enter in the form popped from myConf.cn .</p>
            <p style="font-family: Consolas; font-size: 14px; background-color: #D0D0D0">' . $hash_key . '</p>
            <p>You should finish to submit the form in 30 minutes since you have submit this request.</p>
            <p>If you have not registered an account at myConf.cn, Please ignore this email.</p>
            '
        );
        $CI->email->send();
    }

    /**
     * 更改密码用户逻辑
     * @param string $email
     * @param string $hash_original
     * @param string $hash_to_verify
     * @param string $new_password
     * @throws \myConf\Exceptions\EmailVerifyFailedException
     * @throws \myConf\Exceptions\UserNotExistsException
     */
    public function reset_password(string $email, string $hash_original, string $hash_to_verify, string $new_password): void
    {
        if ($hash_original !== $hash_to_verify) {
            throw new \myConf\Exceptions\EmailVerifyFailedException();
        }
        if (!$this->Models->User->exist_by_email($email)) {
            throw new \myConf\Exceptions\UserNotExistsException();
        }
        $salt = $this->_generate_password_salt();
        $this->Models->User->update_user_password_by_email($email, md5($new_password . $salt), $salt);
        return;
    }

    /**
     * 修改头像业务逻辑
     * @param int $user_id
     * @param string $avatar_field
     * @throws \myConf\Exceptions\AvatarNotSelectedException
     * @throws \myConf\Exceptions\DirectoryException
     * @throws \myConf\Exceptions\FileUploadException
     */
    public function change_avatar(int $user_id, string $avatar_field): void
    {
        try {
            $new_file = \myConf\Libraries\Avatar::parse_avatar($user_id, $avatar_field);
            $this->Models->User->set_avatar($user_id, $new_file);
        } catch (\myConf\Exceptions\FileUploadException $e) {
            if ($e->getShortMessage() === 'NO_SUCH_FILE') {
                throw new \myConf\Exceptions\AvatarNotSelectedException('AVATAR_NOT_SELECTED', 'You have not selected an avatar image to upload');
            }
            throw $e;
        }
        return;
    }

    /**
     * @param int $user_id
     * @return array
     * @throws \myConf\Exceptions\UserNotExistsException
     */
    public function user_full_info(int $user_id) : array
    {
        //先在user表中查找账户信息
        $base_data = $this->Models->User->get_by_id($user_id);
        if (empty($base_data)) {
            throw new \myConf\Exceptions\UserNotExistsException('USER_NOT_EXISTS', 'User with id "' . strval($user_id) . '" does not exist.');
        }
        //再查找对应的scholar信息，如果不存在，返回对象此处对应为空数组
        $scholar_data = $this->Models->Scholar->get_by_email($base_data['user_email']);
        return array(
            'user_id' => $user_id,
            'user_name' => $base_data['user_name'],
            'user_email' => $base_data['user_email'],
            'user_phone' => $base_data['user_phone'],
            'user_avatar' => $base_data['user_avatar'],
            'user_scholar_data' => $scholar_data,
        );
    }

    /**
     * 更新账户的Scholar信息
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @param string $institution
     * @param string $department
     * @param string $address
     * @param string $prefix
     * @param string $chn_full_name
     */
    public function update_scholar_info(string $email, string $first_name, string $last_name, string $institution, string $department, string $address, string $prefix = '', string $chn_full_name = ''): void
    {
        $this->Models->Scholar->set_by_email($email, $first_name, $last_name, $institution, $department, $address, $prefix, $chn_full_name);
        return;
    }

    /**
     * 获取用户的账户信息
     * @param int $user_id
     * @return array
     * @throws \myConf\Exceptions\UserNotExistsException
     */
    public function user_account_info(int $user_id): array
    {
        $user_data = $this->Models->User->get_by_id(strval($user_id));
        if (empty($user_data)) {
            throw new \myConf\Exceptions\UserNotExistsException('USER_NOT_EXISTS', 'The user which has the user_id ' . $user_id . ' does not exist.');
        }
        return $user_data;
    }


    /**
     * 生成密码用的盐
     * @return string
     */
    private function _generate_password_salt(): string
    {
        return md5(uniqid() . strval(time()));
    }
}