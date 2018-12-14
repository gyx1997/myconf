<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/20
 * Time: 20:12
 */

class CF_Controller extends CI_Controller
{
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
     * @var bool 是否使用ajax JSON返回
     */
    protected $_ajax = false;
    /**
     * @var string 当前URL使用BASE64编码后的字串
     */
    protected $_url_encoded = '';
    /**
     * @var bool|string 当前接收到的用于跳转的redirect参数。
     */
    protected $_url_redirect = '';

    /**
     * CF_Controller 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        //加载类库
        $this->load->library('session');
        $this->load->library('Attach', NULL, 'attach');
        //加载模型微服务

        $this->load->helper('url');
        //获得参数
        $this->_url_encoded = base64_encode($this->_get_url());
        $this->_url_redirect = base64_decode($this->input->get_post('redirect'));
        $this->_class = $this->uri->segment(1, '');
        $this->_method = $this->uri->segment(2, '');
        $this->_action = $this->uri->segment(3, '');
        $this->_do = $this->input->get('do');
        $this->_ajax = $this->input->get('ajax') == 'true';
        //检查登录情况
        $this->_check_login();
    }

    /**
     * 获取当前请求的完整URL
     * @return string
     */
    protected function _get_url()
    {
        $sys_protocol = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocol . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }

    /**
     * 检查是否已经登录
     * @return bool
     */
    protected function _check_login()
    {
        $t_uid = $this->session->userdata('user_id');
        if (isset($t_uid)) {
            $this->_user_id = intval($t_uid);
            $this->_login_time = $this->session->userdata('login_time');
            //note:从数据层重新取用户数据, use cache if necessary.
            $this->_current_user = $this->mUser->get_user_by_user_id($this->_user_id);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 登录操作
     * @param $user_data
     */
    protected function _set_login($user_data)
    {
        $this->session->set_userdata('user_id', $user_data['user_id']);
        $this->session->set_userdata('login_time', time());
        $this->_user_id = intval($user_data['user_id']);
    }

    /**
     * 登出操作
     */
    protected function _set_logout()
    {
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('login_time');
    }

    /**
     * 检查是否登录，如果没有登录，跳转到登录页面。
     */
    protected function _login_redirect()
    {
        if (!$this->_has_login()) {
            header('location:/account/login/?redirect=' . $this->_url_encoded);
        }
    }

    /**
     * 检查是否登录。
     * @return bool
     */
    protected function _has_login()
    {
        return $this->_user_id != 0;
    }

    /**
     * 渲染view。
     * @param string $body_view
     * @param string $title
     * @param array $arguments
     */
    protected function _render($body_view, $title = '', $arguments = array())
    {
        $common = array(
            'csrf_name' => $this->security->get_csrf_token_name(),
            'csrf_hash' => $this->security->get_csrf_hash(),
            'url' => $this->_url_encoded,
            //用户登录信息
            'login_status' => $this->_has_login(),
            'login_user' => $this->_current_user,
            'class' => $this->_class,
            'method' => $this->_method,
            'action' => $this->_action,
            'do' => $this->_do
        );
        //渲染页头
        $this->load->view(
            'common/header',
            array_merge(
                array('title' => $this->mConfig->get_title() . ' - ' . $title),
                $common
            )
        );
        //渲染页面
        $this->load->view(
            $body_view,
            array_merge(
                $arguments,
                $common
            )
        );
        //渲染页脚
        $this->load->view(
            'common/footer',
            array(
                'sql_queries' => $this->db->total_queries(),
                'footer1' => $this->mConfig->get_footer1(),
                'footer2' => $this->mConfig->get_footer2(),
                'mitbeian' => $this->mConfig->get_mitbeian()
            )
        );
    }

    /**
     * 跳转到redirect参数指定的页面
     */
    protected function _do_redirect()
    {
        header('location:' . $this->_url_redirect);
        exit();
    }

    /**
     * @deprecated
     * @param string $body_view
     * @param string $title
     * @param array $arguments
     */
    protected function _render_admin($body_view, $title = '', $arguments = array())
    {
        $this->load->view(
            'common/header',
            array(
                'title' => $this->mConfig->get_title() . ' - ' . $title,
                'banner' => $this->mConfig->get_banner(),
            )
        );
        $this->load->view(
            'admin/header',
            array(
                'mod' => $this->uri->segment(2)
            )
        );
        $this->load->view(
            $body_view,
            array_merge(
                $arguments,
                array(
                    'csrf_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash' => $this->security->get_csrf_hash(),
                    'op' => $this->uri->segment(3),
                )
            )
        );
        $this->load->view(
            'admin/footer',
            array(
                'mod' => $this->uri->segment(2)
            )
        );
        $this->load->view(
            'common/footer',
            array(
                'footer1' => $this->mConfig->get_footer1(),
                'footer2' => $this->mConfig->get_footer2(),
                'mitbeian' => $this->mConfig->get_mitbeian()
            )
        );
    }

    protected function _send_mail()
    {

    }

    /**
     * 返回JSON
     * @param array $parameters
     */
    protected function _exit_with_json($parameters = array()): void
    {
        header('Content-Type:application/json;charset=utf-8');
        exit(json_encode($parameters));
    }

    /**
     * 检查验证码
     * @param string $captcha_input
     * @return bool
     */
    protected function _check_captcha(string $captcha_input): bool
    {
        $captcha = $this->session->tempdata('captcha');
        return !empty($captcha) && $captcha === $captcha_input;
    }
}

class CF_Controller_Admin extends CF_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
}
