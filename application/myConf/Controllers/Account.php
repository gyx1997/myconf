<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 16:52
     */

    namespace myConf\Controllers;

    use myConf\Exceptions\HttpStatusException;
    use myConf\Libraries\Session;
    use myConf\Libraries\Env;
    use myConf\Libraries\Logger;

    class Account extends \myConf\BaseController {
        /**
         * Account constructor.
         * @throws \Exception
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 登录
         * @throws \myConf\Exceptions\SendExitInstructionException
         * @throws \myConf\Exceptions\SendRedirectInstructionException
         */
        public function login() {
            switch ($this->_do) {
                case 'submit':
                    {
                        //检查验证码和登录状态
                        $this->_check_captcha($this->input->post('login_captcha')) || $this->exit_promptly(array('status' => 'CAPTCHA_ERR'));
                        $this->_has_login() && $this->exit_promptly(array('status' => 'ALREADY_LOGIN'));
                        !$this->_check_login_times() && $this->exit_promptly(['status' => 'LOGIN_FROZEN']);
                        //使用登录逻辑
                        $status = 'SUCCESS';
                        $redirect = '';
                        $entry = $this->input->post('login_entry');
                        $password = $this->input->post('login_password');
                        try {
                            $current_user = $this->Services->Account->login($entry, $password);
                            $this->_set_login($current_user);
                            $redirect = $this->_url_redirect;
                            $this->_clear_login_times();
                        } catch (\myConf\Exceptions\UserPasswordException $e) {
                            //用户密码错误
                            $status = 'PASSWORD_ERROR';
                            $this->_increment_login_times();    //增加登录错误计数，下同
                            //增加敏感操作日志记录，下同
                            Logger::log_sensitive_operation(Logger::operation_login, Env::get_ip(), 'User "' . $entry . '" login password error.');
                        } catch (\myConf\Exceptions\UserNotExistsException $e) {
                            //用户不存在
                            $status = 'USERNAME_ERROR';
                            $this->_increment_login_times();
                            Logger::log_sensitive_operation(Logger::operation_login, Env::get_ip(), 'User "' . $entry . '" does not exist.');
                        }
                        $this->add_output_variables(array(
                            'status' => $status,
                            'redirect' => $redirect,
                        ), OUTPUT_VAR_JSON_ONLY);
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
         * 登出系统
         * @throws \myConf\Exceptions\SendRedirectInstructionException
         */
        public function logout() {
            $this->_set_logout();
        }

        /**
         * 重置密码
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\HttpStatusException
         * @throws \myConf\Exceptions\SendExitInstructionException
         */
        public function reset_password() {
            switch ($this->_do) {
                case 'verifyKey':
                    {
                        //发送验证邮件
                        $status = 'SUCCESS';
                        $target_email = base64_decode($this->input->get('email'));
                        $this->Services->Account->send_verify_email($target_email, 'reset-pwd', 'myConf Reset Password');
                        $this->add_output_variables(array('status' => $status, 'email' => $target_email));
                        break;
                    }
                case 'submitNewPwd':
                    {
                        //检查验证码
                        $this->_check_captcha($this->input->post('reset_pwd_captcha')) OR $this->exit_promptly(array('status' => 'CAPTCHA_ERR'));
                        //读取输入
                        $status = 'SUCCESS';
                        $hash_key_got = trim($this->input->post('verification_key'));
                        $new_password = $this->input->post('user_password');
                        $email = $this->input->post('user_email');
                        try {
                            $this->Services->Account->check_verify_email('reset-pwd', $hash_key_got);
                            $this->Services->Account->reset_password($email, $new_password);
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
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbTransactionException
         * @throws \myConf\Exceptions\SendExitInstructionException
         * @throws \myConf\Exceptions\SendRedirectInstructionException
         */
        public function register() {
            switch ($this->_do) {
                case 'checkEmail':
                    {
                        $status = 'SUCCESS';
                        $target_email = base64_decode($this->input->get('email'));
                        if ($this->Services->Account->email_exists($target_email)){
                            $status = 'EMAIL_EXISTS';
                        }else {
                            $this->Services->Account->send_verify_email($target_email, 'reg-verify', 'Email Verification');
                        }
                        $this->add_output_variables(['status' => $status]);
                        break;
                    }
                case 'submit':
                    {
                        //检查验证码和登录状态
                        $this->_check_captcha($this->input->post('register_captcha')) || $this->exit_promptly(['status' => 'CAPTCHA_ERR']);
                        $this->_has_login() && $this->exit_promptly(['status' => 'ALREADY_LOGIN']);
                        //得到的验证码
                        $hash_key_got = trim($this->input->post('register_verification_key'));
                        $status = 'SUCCESS';
                        $redirect = $this->_url_redirect;
                        try {
                            $this->Services->Account->check_verify_email('reg-verify', $hash_key_got);
                            $email = $this->input->post('register_email');
                            $password = $this->input->post('register_password');
                            $email_prefix = substr(explode('@', $email)[0], 0, 17);
                            $username = $email_prefix . '-' . substr(md5($email . $password . strval(time())), 0, 32 - strlen($email_prefix) - 1);
                            $user = $this->Services->Account->new_account($email, $username, $password);
                            $this->_set_login($user);
                        } catch (\myConf\Exceptions\EmailExistsException $e) {
                            $status = 'EMAIL_EXISTS';
                        } catch (\myConf\Exceptions\UsernameExistsException $e) {
                            $status = 'USERNAME_EXISTS';
                        } catch (\myConf\Exceptions\EmailVerifyFailedException $e) {
                            $status = 'VERIFY_FAILED';
                        }
                        $this->add_output_variables(
                            [
                                'status' => $status,
                                'redirect' => $redirect
                            ]
                        );
                        break;
                    }
                default:
                    {
                        if ($this->_check_login()) {
                            $this->_redirect_to('/account/');
                            return;
                        }
                        $this->add_output_variables(array('redirect' => base64_encode($this->_url_redirect)));
                        break;
                    }
            }
        }

        /**
         * @throws \myConf\Exceptions\SendRedirectInstructionException
         */
        public function index() : void {
            $this->_redirect_to('/account/my-settings/');
        }

        /**
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DirectoryException
         * @throws \myConf\Exceptions\FileUploadException
         * @throws \myConf\Exceptions\SendRedirectInstructionException
         * @throws \myConf\Exceptions\UserNotExistsException
         */
        public function my_settings() {
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
                $this->_redirect_to('/account/my-settings/?ret=ok');
                return;
            } else {
                $user_data = $this->Services->Account->user_full_info($this->_user_id);
                $this->add_output_variables(array(
                        'user_name' => $user_data['user_name'],
                        'email' => $user_data['user_email'],
                        'avatar' => $user_data['user_avatar'],
                        'scholar_info' => isset($user_data) ? $user_data['user_scholar_data'] : array(),
                    ));
            }
        }

        public function my_conferences() {
            $this->_login_redirect();
        }

        public function my_messages() {
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

        /**
         * 增加一次登录的次数
         */
        private function _increment_login_times() : void {
            $times = Session::get_user_data('login_times');
            Session::set_user_data('login_times', isset($times) ? [
                'expires' => $times['expires'],
                'times' => $times['times'] + 1,
            ] : ['expires' => time() + 900, 'times' => 1]);
        }

        /**
         * 登录次数打到一定限制后暂时不能登录。
         * @return bool
         */
        private function _check_login_times() : bool {
            $times = Session::get_user_data('login_times');
            if (!isset($times)) {
                return true;
            }
            return !($times['expires'] > time() && $times['times'] > 5);
        }

        /**
         * 清除登录次数的记录
         */
        private function _clear_login_times() : void {
            Session::unset_user_data('login_times');
        }
    }