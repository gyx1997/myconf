<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 16:52
 */

namespace myConf\Controllers;


use myConf\Exceptions\HttpStatusException;

class Account extends \myConf\BaseController
{
    /**
     * Account constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 登录
     * @throws \myConf\Exceptions\SendExitInstructionException
     * @throws \myConf\Exceptions\SendRedirectInstructionException
     */
    public function login()
    {
        switch ($this->_do) {
            case 'submit':
                {
                    //检查验证码和登录状态
                    $this->_check_captcha($this->input->post('login_captcha')) || $this->exit_promptly(array('status' => 'CAPTCHA_ERR'));
                    $this->_has_login() && $this->exit_promptly(array('status' => 'ALREADY_LOGIN'));
                    //使用登录逻辑
                    $status = 'SUCCESS';
                    $redirect = '';
                    try {
                        $current_user = $this->Services->Account->login($this->input->post('login_entry'), $this->input->post('login_password'));
                        $this->_set_login($current_user);
                        $redirect = $this->_url_redirect;
                    } catch (\myConf\Exceptions\UserPasswordException $e) {
                        $status = 'PASSWORD_ERROR';
                    } catch (\myConf\Exceptions\UserNotExistsException $e) {
                        $status = 'USERNAME_ERROR';
                    }
                    $this->add_output_variables(array('status' => $status, 'redirect' => $redirect), OUTPUT_VAR_JSON_ONLY);
                    break;
                }
            default:
                {
                    if ($this->_has_login()) {
                        $this->_self_redirect();
                    } else {
                        $this->add_output_variables(array('redirect' => base64_encode($this->_url_redirect)));
                    }
                    break;
                }
        }
    }

    /**
     * @throws \myConf\Exceptions\SendRedirectInstructionException
     */
    public function logout()
    {
        $this->_set_logout();
        //header('location:' . $this->_url_redirect);
    }

    /**
     * 重置密码
     * @throws \myConf\Exceptions\HttpStatusException
     * @throws \myConf\Exceptions\SendExitInstructionException
     */
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
                        $this->Services->Account->send_verify_email($target_email, $hash_key);
                    } catch (\myConf\Exceptions\UserNotExistsException $e) {
                        $status = 'EMAIL_NOT_EXISTS';
                        $this->Session->unset_tempdata('pwd-reset-hash');
                    }
                    $this->add_output_variables(array('status' => $status, 'email' => $target_email));
                    break;
                }
            case 'submitNewPwd':
                {
                    //检查验证码
                    $this->_check_captcha($this->input->post('reset_pwd_captcha')) || $this->exit_promptly(array('status' => 'CAPTCHA_ERR'));
                    //读取输入
                    $status = 'SUCCESS';
                    $hash_key = $this->session->tempdata('pwd-reset-hash');
                    $hash_key_got = trim($this->input->post('verification_key'));
                    $new_password = $this->input->post('user_password');
                    $email = $this->input->post('user_email');
                    try {
                        $this->Services->Account->reset_password($email, $hash_key, $hash_key_got, $new_password);
                    } catch (\myConf\Exceptions\EmailVerifyFailedException $e) {
                        $status = 'EMAIL_VERIFY_FAILED';
                    } catch (\myConf\Exceptions\UserNotExistsException $e) {
                        $status = 'EMAIL_ERROR';
                    }
                    $this->add_output_variables(array('status' => $status));
                    break;
                }
            default:
                {
                    throw new HttpStatusException(400, 'WRONG_DO_FLAG', 'Wrong request parameters (do) given.');
                }
        }
    }

    /**
     * 注册
     * @throws \Exception mysqli_insert_id()出错，认为可以忽略。如果偶然出现会被最外层的_exception_handler抓住。
     */
    public function register()
    {
        switch ($this->_do) {
            case 'submit':
                {
                    //检查验证码和登录状态
                    $this->_check_captcha($this->input->post('register_captcha')) || $this->exit_promptly(array('status' => 'CAPTCHA_ERR'));
                    $this->_has_login() && $this->exit_promptly(array('status' => 'ALREADY_LOGIN'));
                    //使用注册逻辑
                    $status = 'SUCCESS';
                    try {
                        //定义上会出Exception，实际上应该不会。
                        $user = $this->Services->Account->new_account(
                            $this->input->post('register_email'),
                            $this->input->post('register_username'),
                            $this->input->post('register_password')
                        );
                        $this->_set_login($user);
                    } catch (\myConf\Exceptions\EmailExistsException $e) {
                        $status = 'EMAIL_EXISTS';
                    } catch (\myConf\Exceptions\UsernameExistsException $e) {
                        $status = 'USERNAME_EXISTS';
                    }
                    $this->add_output_variables(array('status' => $status));
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
                        $this->_redirect_to('/account/');
                        return;
                    }
                    break;
                }
        }
    }

    /**
     * @throws \myConf\Exceptions\SendRedirectInstructionException
     */
    public function index(): void
    {
        $this->_redirect_to('/account/my-settings/');
    }

    /**
     * @throws \myConf\Exceptions\DirectoryException
     * @throws \myConf\Exceptions\FileUploadException
     * @throws \myConf\Exceptions\SendRedirectInstructionException
     * @throws \sAccount\ScholarNotExistsException
     */
    public function my_settings()
    {
        $this->_login_redirect();
        if ($this->_do == 'submit') {
            switch ($this->_action) {
                case 'general':
                    {
                        //deprecated
                        //$this->Services->Account->update_extra($this->_user_id, array('organization' => $this->input->post('account_org')));
                        break;
                    }
                case 'avatar':
                    {
                        try {
                            $this->Services->Account->change_avatar($this->_user_id, 'avatar_image');
                        } catch (\myConf\Exceptions\AvatarNotSelectedException $e) {
                            //TODO 返回未选择头像
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
                        $this->Services->Account->update_scholar_info($email, $first_name, $last_name, $institution, $department, $address);
                    }
            }
            $this->_redirect_to('/account/my-settings/');
            return;
        } else {
            $user_data = $this->Services->Account->user_full_info($this->_user_id);
            $this->add_output_variables(array(
                    'user_name' => $user_data['user_name'],
                    'email' => $user_data['user_email'],
                    'avatar' => $user_data['user_avatar'],
                    'scholar_info' => isset($user_data) ? $user_data['user_scholar_data'] : array(),
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
                    //$this->_render('account/messages', 'My Account', array());
                    break;
                }
            case 'submit':
                {
                    break;
                }
        }
    }
}