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
        $this->session->sess_destroy();
        header('location:' . $this->_url_redirect);
    }

    public function reset_password()
    {
        switch ($this->_do) {
            case 'verifyKey':
                {
                    $status = 'SUCCESS';
                    $target_email = base64_decode($this->input->get('email'));
                    //生成验证Key
                    $hash_key = md5(uniqid());
                    $this->session->set_tempdata('pwd-reset-hash', $hash_key, 1800);
                    try {
                        $this->sAccount->send_verify_email($target_email, $hash_key);
                    } catch (\sAccount\AccountNotExistsException $e) {
                        $status = 'EMAIL_NOT_EXISTS';
                        $this->session->unset_tempdata('pwd-reset-hash');
                    } catch (\Exception $e) {
                        show_error('Internal Server Error.');
                    }
                    $this->_render('account/reset_pwd_verify', 'Reset Password', array('status' => $status, 'email' => $target_email));
                    break;
                }
            case 'submitNewPwd':
                {
                    //检查验证码
                    $this->_check_captcha($this->input->post('reset_pwd_captcha')) || $this->_exit_with_json(array('status' => 'CAPTCHA_ERR'));
                    //读取输入
                    $status = 'SUCCESS';
                    $hash_key = $this->session->tempdata('pwd-reset-hash');
                    $hash_key_got = trim($this->input->post('verification_key'));
                    $new_password = $this->input->post('user_password');
                    $email = $this->input->post('user_email');
                    try {
                        $this->sAccount->reset_password($email, $hash_key, $hash_key_got, $new_password);
                    } catch (\sAccount\EmailVerifyFailedException $e) {
                        $status = 'EMAIL_VERIFY_FAILED';
                    } catch (\sAccount\AccountNotExistsException $e) {
                        $status = 'EMAIL_ERROR';
                    }
                    $this->_exit_with_json(array('status' => $status));
                    break;
                }
            default:
                {
                    show_404();
                }
        }
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
                    break;
                }
            case 'activate':
                {
                    //TODO 增加注册邮箱激活
                    break;
                }
            default:
                {
                    if ($this->_check_login()) {
                        header('location:/account/');
                        return;
                    }
                    $this->_render('account/register', 'Register', array());
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
                case 'scholar':
                    {
                        $email = $this->input->post('scholarEmail');
                        $first_name = $this->input->post('scholarFirstName');
                        $last_name = $this->input->post('scholarLastName');
                        $institution = $this->input->post('scholarInstitution');
                        $department = $this->input->post('scholarDepartment');
                        $address = $this->input->post('scholarAddress');
                        try {
                            $this->sAccount->update_scholar_info($email, $first_name, $last_name, $institution, $department, $address);
                        } catch (\Exception $e) {
                            show_error('Internal Server Error', 500);
                        }
                    }
            }
            header('location:/account/my-settings/');
            return;
        } else {
            try {
                $user_data = $this->sAccount->user_full_info($this->_user_id);
            } catch (Exception $e) {

            }
            $this->_render('account/settings', 'My Account', array(
                    'scholar_info' => isset($user_data) ? $user_data->assigned_scholar_info : array()
                )
            );
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