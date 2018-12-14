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
        $user = array();
        $status = 'SUCCESS';
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
        $salt = md5(
            substr(
                $this->input->post('register_username'),
                0,
                5
            ) .
            uniqid() .
            strval(time())
        );
        $user_id = $this->mUser->add_user(
            $username,
            md5($password . $salt),
            $email,
            $salt
        );
        try {
            return $this->mUser->get($user_id);
        } catch (Exception $e) {
            throw new Exception('Internal Server Error.', 500);
        }
    }

    /**
     * 修改头像逻辑
     * @param int $user_id
     * @param array $avatar_field
     * @throws \sAccount\AvatarNotSelectedException
     */
    public function change_avatar(int $user_id, array $avatar_field): void
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

    public function user_full_info(int $user_id)
    {

    }
}