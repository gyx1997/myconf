<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 19:18
 */

/**
 * Class ExceptionBase
 */
class ExceptionBase extends \Exception
{
    protected $json_status;

    public function __construct(string $json_status = '', \Throwable $php_previous_exception = null, string $php_message = '', int $php_code = 0)
    {
        parent::__construct($php_message, $php_code, $php_previous_exception);
        $this->json_status = $json_status;
    }

    public function getJsonStatus()
    {
        return $this->json_status;
    }
}