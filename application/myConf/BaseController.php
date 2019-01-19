<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 15:24
 */

namespace myConf;


use myConf\Exceptions\SendExitInstructionException;
use myConf\Libraries\File;
use myConf\Libraries\Session;
use myCOnf\Libraries\Env;

/**
 * Class BaseController
 * @package myConf
 * @author _g63<522975334@qq.com>
 * @version 2019.1
 * @property-read \myConf\Services $Services
 * @property-read \CI_Session $Session
 */
class BaseController
{
    public $input;
    public $uri;
    public $load;
    public $library;
    public $session;

    protected $_login_status = array();
    protected $_parameters = array();
    /**
     * @var int 当前登录的用户ID。
     */
    protected $_user_id = 0;
    /**
     * @var array 当前用户的全部信息。
     */
    protected $_current_user = array();
    /**
     * @var int 登录时间。
     */
    protected $_login_time = 0;
    /**
     * @var mixed|string 控制器类名，URI第2段
     */
    protected $_class = '';
    /**
     * @var mixed|string 方法名，即URI第3段
     */
    protected $_method = '';
    /**
     * @var mixed|string 动作名，即URI第4段
     */
    protected $_action = '';
    /**
     * @var mixed|string 操作名
     */
    protected $_do = '';
    /**
     * @var string 当前URL使用BASE64编码后的字串
     */
    protected $_url_encoded = '';
    /**
     * @var bool|string 当前接收到的用于跳转的redirect参数。
     */
    protected $_url_redirect = '';

    private $_service_manager;

    private $_output_variables = array();

    /**
     * BaseController constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        //下面的加载为了兼容旧的CI代码
        $CI = &get_instance();
        $this->benchmark = $CI->benchmark;
        $this->db = $CI->db;
        $this->email = $CI->email;
        $this->input = $CI->input;
        $this->lang = $CI->lang;
        $this->load = $CI->load;
        $this->output = $CI->output;
        $this->security = $CI->security;
        $this->session = $CI->session;
        $this->uri = $CI->uri;
        $this->zip = $CI->zip;
        //处理URL的路由映射信息
        $special_controller_router_mapping = [
            'conference' => [
                'class' => 1,
                'method' => 3,
                'action' => 4
            ]
        ];
        $this->_class = $this->uri->segment(1, '');
        if (array_key_exists($this->_class, $special_controller_router_mapping)) {
            $mapping_rule = $special_controller_router_mapping[$this->_class];
            $this->_method = strtolower($CI->uri->segment($mapping_rule['method'], ''));
            $this->_action = strtolower($CI->uri->segment($mapping_rule['action'], ''));
        } else {
            $this->_method = strtolower($CI->uri->segment(2, ''));
            $this->_action = strtolower($CI->uri->segment(3, ''));
        }
        if (strlen($this->_method) === 0) {
            $this->_method = 'index';
        }
        if (strlen($this->_action) === 0) {
            $this->_action = 'default';
        }
        //加载微服务管理器
        $this->_service_manager = new \myConf\Services;
        //获得参数
        $this->_do = $this->input->get('do');
        $this->_url_encoded = base64_encode(Env::get_current_url());
        $url_redirect = base64_decode(Env::get_redirect());
        $this->_url_redirect = $url_redirect === '' ? '/' : $url_redirect;
        //检查登录情况
        $this->_check_login();
    }

    public function check_authority() : void {

    }

    /**
     * @param array &$ret_vars
     * @throws \myConf\Exceptions\HttpStatusException
     */
    public final function run(array &$ret_vars): void
    {
        $method = str_replace('-', '_', $this->_method);
        if (!method_exists($this, $method)) {
            throw new \myConf\Exceptions\HttpStatusException(404, 'METHOD_NOT_FOUND', 'Method "' . $method . '" from requested URL not found.');
        }
        //执行子方法
        $this->$method();
        //返回数据
        $this->_collect_output_variables();
        $ret_vars = $this->_output_variables;
        return;
    }

    /**
     * 获取输出变量列表
     */
    protected function _collect_output_variables(): void
    {
        $this->add_output_variables(
            array(
                'title' => $this->Services->Config->get_title(),
                'footer1' => $this->Services->Config->get_footer(),
                'mitbeian' => $this->Services->Config->get_mitbeian(),
                'csrf_name' => $this->security->get_csrf_token_name(),
                'csrf_hash' => $this->security->get_csrf_hash(),
                'url' => $this->_url_encoded,
                'login_status' => $this->_has_login(),
                'login_user' => $this->_current_user,
                'class' => $this->_class,
                'method' => $this->_method,
                'action' => $this->_action,
                'do' => $this->_do
            ),
            OUTPUT_VAR_HTML_ONLY
        );
    }

    /**
     * 将变量添加入输出列表
     * @param array $vars 一组变量
     * @param int $type 变量最终输出方式，分别为全部输出，只在HTML输出，只在JSON输出。
     */
    public final function add_output_variables(array $vars = array(), int $type = OUTPUT_VAR_ALL): void
    {
        foreach ($vars as $key => $value) {
            $this->_output_variables[$key] = array('type' => $type, 'value' => $value);
        }
    }

    /**
     * @return string
     */
    public function template_name(): string
    {
        $method = ucfirst(str_replace('-', '_', $this->_method));
        $action = ucfirst(str_replace('-', '_', $this->_action));
        return ucfirst($this->_class) . DIRECTORY_SEPARATOR . ucfirst($method) . DIRECTORY_SEPARATOR . ucfirst($action);
    }

    /**
     * dummy Method. Should be over-written in Derived Controllers
     */
    public function index(): void
    {
        return;
    }

    /**
     * 魔术方法，获取控制器使用的类
     * @param $key
     * @return \CI_Session|\myConf\Services|null
     */
    public function __get($key) {
        if ($key === 'Services') {
            return $this->_service_manager;
        } else if ($key === 'Session') {
            return $this->session;
        } else {
            return null;
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function _check_login(): bool
    {
        try {
            $this->_user_id = intval(Session::get_user_data('user_id'));
            if ($this->_user_id === 0) {
                return FALSE;
            }
            $this->_login_time = Session::get_user_data('login_time');
            $this->_current_user = $this->Services->Account->user_account_info($this->_user_id);
            //避免session失效问题，刷新session
            $this->_set_login($this->_current_user);
            return TRUE;
        } catch (\myConf\Exceptions\SessionKeyNotExistsException $e) {
            return FALSE;
        } catch (\myConf\Exceptions\UserNotExistsException $e) {
            throw new \Exception('User has logged in, but we cannot get his/her account data. Check whether cache layer goes wrong, or there has been a web attack.');
        }
    }

    /**
     * 登录操作
     * @param array $user_data
     */
    protected function _set_login(array $user_data): void
    {
        Session::set_user_data('user_id', $user_data['user_id']);
        Session::set_user_data('login_time', time());
        $this->_user_id = intval($user_data['user_id']);
    }

    /**
     * @throws Exceptions\SendRedirectInstructionException
     */
    protected function _set_logout(): void
    {
        Session::destroy();
        $this->_self_redirect();
    }

    /**
     * 检查是否登录，如果没有登录，跳转到登录页面。
     * @throws Exceptions\SendRedirectInstructionException
     */
    protected function _login_redirect() : void
    {
        if (!$this->_has_login()) {
            $this->_redirect_to('/account/login/?redirect=' . $this->_url_encoded);
            exit();
        }
    }

    /**
     * 检查是否登录。
     * @return bool
     */
    protected function _has_login(): bool
    {
        return $this->_user_id !== 0;
    }

    /**
     * 抛出异常，进行跳转
     * @throws Exceptions\SendRedirectInstructionException
     */
    protected function _self_redirect(): void
    {
        //exit($this->_url_redirect);
        throw new \myConf\Exceptions\SendRedirectInstructionException($this->_url_redirect === '' ? '/' : $this->_url_redirect);
    }

    /**
     * 跳转到指定的URL。
     * @param string $target
     * @throws Exceptions\SendRedirectInstructionException
     */
    protected function _redirect_to(string $target): void
    {
        throw new \myConf\Exceptions\SendRedirectInstructionException($target);
    }

    /**
     * 检查验证码
     * @param string $captcha_input
     * @return bool
     */
    protected function _check_captcha(string $captcha_input): bool
    {
        $captcha = Session::get_temp_data('captcha');
        return !empty($captcha) && $captcha === $captcha_input;
    }

    /**
     * 根据指定的返回值提前退出并输出Json
     * @param array $data
     * @throws Exceptions\SendExitInstructionException
     */
    protected function _exit_with_json(array $data = array()): void
    {
        $this->add_output_variables($data, OUTPUT_VAR_JSON_ONLY);
        throw new \myConf\Exceptions\SendExitInstructionException(\myConf\Exceptions\SendExitInstructionException::DO_OUTPUT_JSON, 'RET_JSON', 'Return with json called.');
    }

    /**
     * 立即退出执行
     * @param null $data
     * @throws SendExitInstructionException
     */
    public function exit_promptly($data = null)
    {
        if (isset($data)) {
            throw new SendExitInstructionException(\myConf\Exceptions\SendExitInstructionException::DO_OUTPUT_JSON, $data);
        }
        throw new SendExitInstructionException();
    }
}