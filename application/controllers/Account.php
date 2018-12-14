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
                    //检查验证码和登录状态
                    $this->_check_captcha($this->input->post('login_captcha')) || $this->_exit_with_json(array('status' => 'CAPTCHA_ERR'));
                    $this->_has_login() && $this->_exit_with_json(array('status' => 'ALREADY_LOGIN'));
                    //使用登录逻辑
                    $status = 'SUCCESS';
                    $redirect = '';
                    try {
                        $current_user = $this->sAccount->login($this->input->post('login_entry'), $this->input->post('login_password'));
                        $this->_set_login($current_user);
                        $redirect = $this->_url_redirect;
                    } catch (\sAccount\AccountPasswordErrorException $e) {
                        $status = 'PASSWORD_ERROR';
                    } catch (\sAccount\LoginEntryNotExistsException $e) {
                        $status = 'USERNAME_ERROR';
                    }
                    $this->_exit_with_json(array('status' => $status, 'redirect' => $redirect));
                    break;
                }
            default:
                {
                    $this->_has_login() || $this->_render(
                            'account/login',
                            'Login',
                            array('redirect' => base64_encode($this->_url_redirect)
                            )
                        );
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
                    //检查验证码和登录状态
                    $this->_check_captcha($this->input->post('register_captcha')) || $this->_exit_with_json(array('status' => 'CAPTCHA_ERR'));
                    $this->_has_login() && $this->_exit_with_json(array('status' => 'ALREADY_LOGIN'));
                    //使用注册逻辑
                    $status = 'SUCCESS';
                    try {
                        $user = $this->sAccount->register(
                            $this->input->post('register_email'),
                            $this->input->post('register_username'),
                            $this->input->post('register_password')
                        );
                        $this->_set_login($user);
                    } catch (\sAccount\EmailAlreadyExistsException $e) {
                        $status = 'EMAIL_EXISTS';
                    } catch (\sAccount\UsernameAlreadyExistsException $e) {
                        $status = 'USERNAME_EXISTS';
                    } catch (\Exception $e) {
                        $status = 'INTERNAL_SERVER_ERROR';
                    }
                    $this->_exit_with_json(array('status' => $status));
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
                        try {
                            $this->sAccount->change_avatar($this->_user_id, 'avatar_image');
                        } catch (\Exception $e) {

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