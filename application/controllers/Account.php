<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/28
 * Time: 10:07
 */

defined('BASEPATH') OR exit('Access Denied.');

class account extends CF_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 登录页面
     */
    public function login()
    {
        switch ($this->_do) {
            case 'submit':
                {
                    $captcha = $this->session->tempdata('captcha');
                    if (empty($captcha) || $captcha !== $this->input->post('login_captcha')) {
                        $this->_exit_with_json(array('status' => 'CAPTCHA_ERR'));
                    }

                    if ($this->_has_login()) {
                        $this->_exit_with_json(array('status' => 'ALREADY_LOGIN'));
                    }

                    //同时支持用户名和邮箱登录
                    $entry = $this->input->post('login_entry');
                    $user = array();
                    if ($this->mUser->exists_username($entry)) {
                        $user = $this->mUser->get_user_by_username($entry);
                    } else if ($this->mUser->exists_email($entry)) {
                        $user = $this->mUser->get_user_by_email($entry);
                    } else {
                        $this->_exit_with_json(array('status' => 'USERNAME_ERROR'));
                        return;
                    }

                    $password_got = $this->input->post('login_password');
                    if ($user['user_password'] == md5($password_got . $user['password_salt'])) {
                        $this->_set_login($user);
                        $this->_exit_with_json(
                            array(
                                'status' => 'SUCCESS',
                                'redirect' => $this->_url_redirect
                            )
                        );
                    } else {
                        $this->_exit_with_json(array('status' => 'PASSWORD_ERROR', 'pwd' => md5($password_got . $user['password_salt'])));
                    }
                    break;
                }
            default:
                {
                    if (!$this->_has_login()) {
                        $this->_render(
                            'account/login',
                            'Login',
                            array('redirect' => base64_encode($this->_url_redirect)
                            )
                        );
                    }
                    break;
                }
        }
    }

    public function logout()
    {
        $this->_set_logout();
        header('location:' . $this->_url_redirect);
    }

    /**
     * 注册页面
     */
    public function register()
    {
        switch ($this->_do) {
            case 'submit':
                {
                    $captcha = $this->session->tempdata('captcha');
                    if (empty($captcha) || $captcha !== $this->input->post('register_captcha')) {
                        $this->_exit_with_json(array('status' => 'CAPTCHA_ERR'));
                        return;
                    }
                    if ($this->mUser->exists_username($this->input->post('register_username'))) {
                        $this->_exit_with_json(array('status' => 'USERNAME_EXISTS'));
                        return;
                    }
                    if ($this->mUser->exists_email($this->input->post('register_email'))) {
                        $this->_exit_with_json(array('status' => 'EMAIL_EXISTS'));
                        return;
                    }
                    $salt = md5(substr(
                            $this->input->post('register_username'),
                            0,
                            5
                        ) .
                        uniqid() .
                        strval(time())
                    );

                    $this->mUser->add_user(
                        $this->input->post('register_username'),
                        md5($this->input->post('register_password') . $salt),
                        $this->input->post('register_email'),
                        $salt
                    );

                    $user = $this->mUser->get_user_by_username($this->input->post('register_username'));
                    $this->_set_login($user);

                    $this->_exit_with_json(array('status' => 'SUCCESS'));


                    //$this->load->library('email');
                    //$this->email->from('csqrwc@126.com', 'CSQRWC Register');
                    //$this->email->to($this->input->post('email_text'));
                    //$this->email->subject('Email Test');
                    //$this->email->message('Testing the email class.');
                    //$this->email->send();
                    break;
                }
            case 'activate':
                {
                    break;
                }
            default:
                {
                    if ($this->_check_login()) {
                        header('location:/account/');
                        return;
                    }
                    $this->_render(
                        'account/register',
                        'Register',
                        array()
                    );
                    break;
                }
        }
    }

    public function index()
    {
        header('location:/account/my-settings/');
    }

    public function my_settings()
    {
        $this->_login_redirect();
        if ($this->_do == 'submit') {
            switch ($this->_action) {
                case 'general':
                    {
                        $this->mUser->update_extra($this->_user_id, array('organization' => $this->input->post('account_org')));
                        break;
                    }
                case 'avatar':
                    {
                        //检查文件夹是否存在
                        $base_path = AVATAR_DIR . strval($this->_user_id % 100);
                        if (!is_dir($base_path)) {
                            @mkdir($base_path);
                        }
                        //上传文件
                        $image_param['upload_path'] = TMP_PATH;
                        $image_param['allowed_types'] = 'jpeg|jpg|png';
                        $image_param['max_size'] = '1048576';
                        $this->load->library('upload', $image_param);
                        if ($this->upload->do_upload('avatar_image')) {
                            $image_data = $this->upload->data();
                            $new_image_name = $this->_user_id . $image_data['file_ext'];
                            rename($image_data['full_path'], $base_path . '/' . $new_image_name);
                            @unlink($image_data['full_path']);
                            //更新数据库
                            $this->mUser->update_extra(
                                $this->_user_id,
                                array('avatar' => '/' . strval($this->_user_id % 100) . '/' . $new_image_name)
                            );
                        }
                        break;
                    }
            }
            header('location:/account/my-settings/');
            return;
        } else {
            $this->_render('account/settings', 'My Account', array());
        }
    }

    public function my_conferences()
    {
        $this->_login_redirect();
    }

    public function my_messages()
    {
        switch ($this->_do) {
            case '':
                {
                    $this->_render('account/messages', 'My Account', array());
                    break;
                }
            case 'submit':
                {
                    break;
                }

        }
    }
}