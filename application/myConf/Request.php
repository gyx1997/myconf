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

    private $_ajax;

    public function __construct()
    {
        //返回变量
        $ret = 0;
        //加载输出响应类
        $this->_response = new \myConf\Response();
        //使用实例化的CI核心类处理URL,为加载核心控制器模块做准备
        $CI = &get_instance();
        $this->_ajax = $CI->input->get('ajax') === 'true';
        $controller_str = ucfirst(strtolower($CI->uri->segment(1, '')));
        $controller_class_name = '\\myConf\\Controllers\\' . $controller_str;
        //尝试加载控制器，并将控制权交给控制器
        try {
            /**
             * @var \myConf\BaseController $controller 实例化的控制器
             */
            $this->_controller = new $controller_class_name();
            $vars = array();
            $this->_controller->run($vars);
            //输出响应
            $this->_response->add_variables($this->_parse_variables($vars));
            $this->_ajax === true ? $this->_response->json($vars) : $this->_response->html($this->_controller->template_name());
        } catch (\myConf\Exceptions\SendRedirectInstructionException $e) {
            //跳转指令
            header('location:' . $e->getRedirectURL());
        } catch (\myConf\Exceptions\SendExitInstructionException $e) {
            if ($e->getAction() === \myConf\Exceptions\SendExitInstructionException::DO_OUTPUT_JSON) {
                $this->_response->add_variables($e->getData());
                $this->_response->json();
            }
        } catch (\myConf\Exceptions\HttpStatusException $e) {
            $this->_response->handled_error($e->getMessage(), $e->getHttpStatus());
        } catch (\Throwable $e) {
            //控制器模块加载失败，或者控制器本身无法处理的异常，的错误处理。这里都是按照 HTTP 500 来处理。
            log_message('ERROR', $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $str = 'A fatal error occurred that your request could not be processed properly. We are so sorry for the inconvenience we have caused. <br/> Critical Information has been written down to our logging system to help us analyze and solve this problem. <br/> If you have further questions, please contact us with the email : xxxx@xxx.com. ';
            if (ENVIRONMENT === 'production') {
                $this->_response->handled_error($str, 500);
            } else {
                $trace_str = '';
                foreach ($e->getTrace() as $trace) {
                    $trace_str .= '<div><span style="font-family:Consolas;">' . (isset($trace['class']) ? $trace['class'] : '') . '::' . $trace['function'] . '</span><p>Line ' . $trace['line'] . ' in File ' . $trace['file'] . '</p></div>';
                }

                $this->_response->handled_error(
                    $str .
                    '<div style="border: 1px #333333 solid; padding: 10px;"><h3>Debug Information</h3><p>' . $e->getMessage() . '</p><p> At Line : <strong>' . $e->getLine() . '</strong> , in File : ' . $e->getFile() . '</p><p> <strong>BackTrace</strong> : </p>' . $trace_str . '</p></div>', 500);
            }
            $ret = -1;
        }
        exit($ret);
    }

    private function _parse_variables(array $vars_from_controller): array
    {
        $result = array();
        foreach ($vars_from_controller as $key => $var) {
            if (
                $var['type'] == OUTPUT_VAR_ALL ||
                ($var['type'] === OUTPUT_VAR_HTML_ONLY && $this->_ajax === false) ||
                ($var['type'] === OUTPUT_VAR_JSON_ONLY && $this->_ajax === true)
            ) {
                $result[$key] = $var['value'];
            }
        }
        return $result;
    }
}