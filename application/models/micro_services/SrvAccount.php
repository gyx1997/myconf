<?php

/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 9:35
 */
class sAccount extends CF_Service
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 登录逻辑
     * @param string $entry
     * @param string $password
     * @return array
     * @throws \sAccount\AccountPasswordErrorException
     * @throws \sAccount\LoginEntryNotExistsException
     */
    public function login(string $entry, string $password): array
    {
        if ($this->mUser->exists_username($entry)) {
            $user = $this->mUser->get_user_by_username($entry);
        } else if ($this->mUser->exists_email($entry)) {
            $user = $this->mUser->get_user_by_email($entry);
        } else {
            throw new \sAccount\LoginEntryNotExistsException();
        }
        if ($user['user_password'] !== md5($password . $user['password_salt'])) {
            throw new \sAccount\AccountPasswordErrorException();
        }
        return $user;
    }

    /**
     * 登录逻辑
     * @param string $email
     * @param string $username
     * @param string $password
     * @return array
     * @throws Exception
     * @throws \sAccount\EmailAlreadyExistsException
     * @throws \sAccount\UsernameAlreadyExistsException
     */
    public function register(string $email, string $username, string $password): array
    {
        if ($this->mUser->exists_email($email)) {
            throw new \sAccount\EmailAlreadyExistsException();
        }
        if ($this->mUser->exists_username($username)) {
            throw new \sAccount\UsernameAlreadyExistsException();
        }
        $salt = $this->_generate_password_salt();
        $this->mUser->begin_transaction();
        $user_id = $this->mUser->add_user($username, md5($password . $salt), $email, $salt);
        $this->mScholar->add_scholar_info($email, '', '', '', '', '', '');                      //添加文章学者信息，需要自行完善
        $this->mUser->end_transaction();
        try {
            return $this->mUser->get($user_id);
        } catch (\DbNotFoundException $e) {
            throw new Exception('mysqli_insert_id() called in method `register` got incorrect return value, which caused the next SELECT operation return empty set.');
        }
    }

    /**
     * 发送验证邮件
     * @param string $email
     * @param string $hash_key
     * @throws \sAccount\AccountNotExistsException
     */
    public function send_verify_email(string $email, string $hash_key): void
    {
        if (!$this->mUser->exists_email($email)) {
            throw new \sAccount\AccountNotExistsException();
        }
        $this->load->library('email');
        $this->email->from('csqrwc@126.com', 'myConf Password Reset');
        $this->email->to($email);
        $this->email->subject('Password Reset');
        $this->email->message(
            '
            <h1>Email Verification From myConf.cn</h1>
            <p>Copy the text below to enter in the form popped from myConf.cn .</p>
            <p style="font-family: Consolas; font-size: 14px; background-color: #D0D0D0">' . $hash_key . '</p>
            <p>You should finish to submit the form in 30 minutes since you have submit this request.</p>
            <p>If you have not registered an account at myConf.cn, Please ignore this email.</p>
            '
        );
        $this->email->send();
    }

    /**
     * 更改密码逻辑
     * @param string $email
     * @param string $hash_original
     * @param string $hash_to_verify
     * @param string $new_password
     * @throws \sAccount\AccountNotExistsException
     * @throws \sAccount\EmailVerifyFailedException
     */
    public function reset_password(string $email, string $hash_original, string $hash_to_verify, string $new_password): void
    {
        if (!$this->mUser->exists_email($email)) {
            throw new \sAccount\AccountNotExistsException();
        }
        if ($hash_original !== $hash_to_verify) {
            throw new \sAccount\EmailVerifyFailedException();
        }
        $salt = $this->_generate_password_salt();
        $this->mUser->update_user_password_by_email($email, md5($new_password . $salt), $salt);
        return;
    }

    /**
     * 修改头像逻辑
     * @param int $user_id
     * @param string $avatar_field
     * @throws \sAccount\AvatarNotSelectedException
     */
    public function change_avatar(int $user_id, string $avatar_field): void
    {
        //检查文件夹是否存在
        $base_path = AVATAR_DIR . strval($user_id % 100);
        if (!is_dir($base_path)) {
            @mkdir($base_path);
        }
        //上传头像文件
        $image_param['upload_path'] = TMP_PATH;
        $image_param['allowed_types'] = 'jpeg|jpg|png';
        $image_param['max_size'] = '1048576';
        $this->load->library('upload', $image_param);
        if ($this->upload->do_upload($avatar_field)) {
            $image_data = $this->upload->data();
            $new_image_name = $user_id . $image_data['file_ext'];
            rename($image_data['full_path'], $base_path . '/' . $new_image_name);
            @unlink($image_data['full_path']);
            //更新数据库
            $this->mUser->update_extra(
                $user_id,
                array('avatar' => '/' . strval($user_id % 100) . '/' . $new_image_name)
            );
        } else {
            throw new \sAccount\AvatarNotSelectedException();
        }
        return;
    }

    /**
     * 获取用户的完全信息
     * @param int $user_id
     * @return \sAccount\sAccountUserFullInfoRet
     * @throws \sAccount\AccountNotExistsException
     */
    public function user_full_info(int $user_id): \sAccount\sAccountUserFullInfoRet
    {
        //先在user表中查找账户信息
        try {
            $base_data = $this->mUser->get_user_by_user_id($user_id);
        } catch (DbNotFoundException $e) {
            throw new \sAccount\AccountNotExistsException();
        }
        //再查找对应的scholar信息，如果不存在，返回对象此处对应为空数组
        try {
            $scholar_data = $this->mScholar->get_scholar_info($base_data['user_email']);
            $ret = new \sAccount\sAccountUserFullInfoRet(
                $user_id,
                $base_data['user_name'],
                $base_data['user_email'],
                $base_data['user_phone'],
                $base_data['avatar'],
                $base_data['organization'],
                $scholar_data
            );
        } catch (DbNotFoundException $e) {
            $ret = new \sAccount\sAccountUserFullInfoRet(
                $user_id,
                $base_data['user_name'],
                $base_data['user_email'],
                $base_data['user_phone'],
                $base_data['avatar'],
                $base_data['organization']
            );
        }
        return $ret;
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
     * @throws \sAccount\ScholarNotExistsException
     */
    public function update_scholar_info(string $email, string $first_name, string $last_name, string $institution, string $department, string $address, string $prefix = '', string $chn_full_name = ''): void
    {
        if (!$this->mScholar->scholar_exists($email)) {
            throw new \sAccount\ScholarNotExistsException();
        }
        $this->mScholar->update_scholar($email, $first_name, $last_name, $institution, $department, $address, $prefix, $chn_full_name);
        return;
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