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

    //加载请求，开始应用逻辑响应
    $request = new \myConf\Request();