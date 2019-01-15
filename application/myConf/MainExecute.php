<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 15:17
     */

    //定义myConf常量
    define('MY_CONF', true);
    //变量输出方式
    define('OUTPUT_VAR_ALL', 0);
    define('OUTPUT_VAR_HTML_ONLY', 1);
    define('OUTPUT_VAR_JSON_ONLY', 2);
    define('HTTP_STATUS_CODE', array(
        100 => 'Continue',
        101 => 'Switching Protocols',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        511 => 'Network Authentication Required',
    ));

    //注册自动加载器
    spl_autoload_register(function ($class_name) {
        $file_to_load = APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
        if (file_exists($file_to_load)) {
            include $file_to_load;
            return true;
        }
        return false;
    });

    //初始化静态数据库类
    \myConf\Libraries\DbHelper::init();
    //初始化静态会话类
    \myConf\Libraries\Session::init();

    /**
     * @var int 进程返回值
     */
    $ret = 0;

    /**
     * @var \CI_Controller 当前的CI超级对象
     */
    $CI = &get_instance();
    $ajax = $CI->input->get('ajax') === 'true';
    $controller_name = ucfirst(strtolower($CI->uri->segment(1, '')));
    $controller_name === '' && $controller_str = 'Home';
    /**
     * @var \myConf\BaseController $controller 实例化的请求
     */
    $request = new \myConf\Request($controller_name);
    /**
     * @var \myConf\Response $response 实例化的响应
     */
    $response = new \myConf\Response();
    //加载请求，开始应用逻辑响应
    //这里把所有指令和异常均外置，在最外层捕获各种指令、错误、异常
    //不放在内部，因为ClassNotFoundException是由request抛出的。
    try {
        $request->run();
        $vars = $request->result_variables();
        $result = [];
        foreach ($vars as $key => $var) {
            if ($var['type'] == OUTPUT_VAR_ALL || ($var['type'] === OUTPUT_VAR_HTML_ONLY && $ajax === false) || ($var['type'] === OUTPUT_VAR_JSON_ONLY && $ajax === true)) {
                $result[$key] = $var['value'];
            }
        }
        $response->add_variables($result);
        $response->add_variables(['StaticUrl' => '']);
        $ajax ? $response->json() : $response->html($request->template_name());
    } catch (\myConf\Exceptions\SendRedirectInstructionException $e) {
        //跳转指令
        header('location:' . $e->getRedirectURL());
    } catch (\myConf\Exceptions\SendExitInstructionException $e) {
        //直接退出指令
        if ($e->getAction() === \myConf\Exceptions\SendExitInstructionException::DO_OUTPUT_JSON) {
            $response->add_variables($e->getData());
            $response->json();
        }
    } catch (\myConf\Exceptions\ClassNotFoundException $e) {
        //控制器没有找到，理论上是返回404的
        $response->handled_error('The path you requested was not found.', 404);
    } catch (\myConf\Exceptions\HttpStatusException $e) {
        //HTTP错误
        $response->handled_error($e->getMessage(), $e->getHttpStatus());
    } catch (\Throwable $e) {
        //控制器模块加载失败，或者控制器本身无法处理的异常，的错误处理。这里都是按照 HTTP 500 来处理。
        log_message('ERROR', $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        $str = 'A fatal error occurred that your request could not be processed properly. We are so sorry for the inconvenience we have caused. <br/> Critical Information has been written down to our logging system to help us analyze and solve this problem. <br/> If you have further questions, please contact us with the email : xxxx@xxx.com. ';
        if (ENVIRONMENT === 'production') {
            $response->handled_error($str, 500);
        } else {
            $trace_str = '';
            foreach ($e->getTrace() as $trace) {
                $trace_str .= '<div><span style="font-family:Consolas;">' . (isset($trace['class']) ? $trace['class'] : '') . '::' . $trace['function'] . '</span><p>Line ' . $trace['line'] . ' in File ' . $trace['file'] . '</p></div>';
            }
            $response->handled_error($str . '<div style="border: 1px #333333 solid; padding: 10px;"><h3>Debug Information</h3><p>' . $e->getMessage() . '</p><p> At Line : <strong>' . $e->getLine() . '</strong> , in File : ' . $e->getFile() . '</p><p> <strong>BackTrace</strong> : </p>' . $trace_str . '</p></div>', 500);
        }
        $ret = -1;
    }
    exit($ret);