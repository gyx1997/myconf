<?php

namespace myConf;


class Request
{
    /**
     * @var Response $_response 响应管理器
     */
    private $_response;

    /**
     * @var \myConf\BaseController $controller 控制器
     */
    private $_controller;

    /**
     * @var string 控制器名称
     */
    private $_controller_str;

    /**
     * @var string 控制器完整类名
     */
    private $_controller_class_name;

    /**
     * @var bool 是否是ajax请求
     */
    private $_ajax;

    /**
     * @var array 当前所有的变量
     */
    private $_vars;

    /**
     * Request constructor.
     * @param string $default_controller 默认的控制器
     */
    public function __construct(string $default_controller = 'Home')
    {
        //返回变量
        $ret = 0;
        //加载输出响应类
        $this->_response = new \myConf\Response();
        //使用实例化的CI核心类处理URL,为加载核心控制器模块做准备
        $CI = &get_instance();
        $this->_ajax = $CI->input->get('ajax') === 'true';
        $controller_str = ucfirst(strtolower($CI->uri->segment(1, '')));
        $this->_controller_str === '' && $controller_str = $default_controller;
        $this->_controller_class_name = '\\myConf\\Controllers\\' . $controller_str;
    }

    /**
     * 得到变量
     * @param array $vars_from_controller
     * @return array
     */
    private function _parse_variables(array $vars_from_controller) : array {
        $result = array();
        foreach ($vars_from_controller as $key => $var) {
            if ($var['type'] == OUTPUT_VAR_ALL || ($var['type'] === OUTPUT_VAR_HTML_ONLY && $this->_ajax === false) || ($var['type'] === OUTPUT_VAR_JSON_ONLY && $this->_ajax === true)) {
                $result[$key] = $var['value'];
            }
        }
        return $result;
    }

    /**
     * 返回response对象
     * @return \myConf\Response
     */
    public function response() : \myConf\Response {
        return $this->_response;
    }

    /**
     * @throws \myConf\Exceptions\ClassNotFoundException
     * @throws \myConf\Exceptions\URLRequestException
     */
    public function run() : void {
        if (!class_exists($this->_controller_class_name)) {
            //尝试加载控制器，并将控制权交给控制器
            throw new \myConf\Exceptions\ClassNotFoundException('CLASS_NOT_FOUND', 'Requested controller class not found.');
        }
        try {
            $class = $this->_controller_class_name;
            $this->_controller = new $class();
            $vars = [];
            $this->_controller->run($vars);
            $this->_response->add_variables($this->_parse_variables($vars));
            $this->_ajax === true ? $this->_response->json() : $this->_response->html($this->_controller->template_name());
        } catch (\myConf\Exceptions\SendRedirectInstructionException $e) {
            //跳转指令
            header('location:' . $e->getRedirectURL());
        } catch (\myConf\Exceptions\SendExitInstructionException $e) {
            //直接退出指令
            if ($e->getAction() === \myConf\Exceptions\SendExitInstructionException::DO_OUTPUT_JSON) {
                $this->_response->add_variables($e->getData());
                $this->_response->json();
            }
        }
    }

    /**
     * @param string $message
     * @param int $code
     */
    public function show_error(string $message, int $code) : void {
        $this->_response->handled_error($message, $code);
    }
}